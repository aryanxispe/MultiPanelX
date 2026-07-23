<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit();
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$success = '';
$error = '';

// Handle key purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_key'])) {

    $keyId = (int)$_POST['key_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Get key details
        $stmt = $pdo->prepare("SELECT lk.*, m.name as mod_name FROM license_keys lk 
                              LEFT JOIN mods m ON lk.mod_id = m.id 
                              WHERE lk.id = ? AND lk.status = 'available'");
        $stmt->execute([$keyId]);
        $key = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$key) {
            throw new Exception("Key not available");
        }
        
        // Check user balance
        if ($user['balance'] < $key['price']) {
            throw new Exception("Insufficient balance");
        }
        
        // Update key status
        $stmt = $pdo->prepare("UPDATE license_keys SET status = 'sold', sold_to = ?, sold_at = NOW() WHERE id = ?");
        $stmt->execute([$userId, $keyId]);
        
        // Deduct balance
        $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
        $stmt->execute([$key['price'], $userId]);
        
        // Add transaction
        $reference = "License purchase #" . $keyId;
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, reference, status) VALUES (?, ?, 'purchase', ?, 'completed')");
        $stmt->execute([$userId, -$key['price'], $reference]);
        
        $pdo->commit();
        $success = 'License key purchased successfully!';
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['balance'] = $user['balance']; // Sync session balance
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get filter parameters
$modId = $_GET['mod_id'] ?? '';

// Get all mods for filter dropdown
$stmt = $pdo->query("SELECT * FROM mods WHERE status = 'active' ORDER BY name");
$mods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available keys
$where = "lk.status = 'available'";
$params = [];

if ($modId) {
    $where .= " AND lk.mod_id = ?";
    $params[] = $modId;
}

$sql = "SELECT lk.*, m.name as mod_name 
        FROM license_keys lk 
        LEFT JOIN mods m ON lk.mod_id = m.id 
        WHERE $where 
        ORDER BY lk.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$availableKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's purchased keys
$stmt = $pdo->prepare("SELECT lk.*, m.name as mod_name 
                      FROM license_keys lk 
                      LEFT JOIN mods m ON lk.mod_id = m.id 
                      WHERE lk.sold_to = ? 
                      ORDER BY lk.sold_at DESC");
$stmt->execute([$userId]);
$purchasedKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functions
function formatCurrency(float $amount) {
    return '₹' . number_format($amount, 2);
}

function formatDate(string $date) {
    return date('d M Y H:i', strtotime($date));
}

$page_title = "Manage Keys - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-glow-primary">License Key Manager</h2>
        <p class="text-sm text-brand-muted mt-1">Browse available keys or manage your purchased licenses.</p>
    </div>
    
    <!-- Filter -->
    <form method="GET" class="flex items-center space-x-3 w-full md:w-auto">
        <select name="mod_id" onchange="this.form.submit()" class="px-4 py-2 bg-brand-surface border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary w-full md:w-56">
            <option value="">All Applications</option>
            <?php foreach ($mods as $mod): ?>
                <option value="<?php echo $mod['id']; ?>" <?php echo $modId == $mod['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($mod['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Alerts -->
<?php if ($error): ?>
    <div class="bg-brand-error/10 border border-brand-error/20 text-brand-error text-xs rounded-xl p-3.5 mb-6 flex items-center space-x-2.5">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-brand-success/10 border border-brand-success/20 text-brand-success text-xs rounded-xl p-3.5 mb-6 flex items-center space-x-2.5">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span><?php echo htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<!-- Tabs/Grids -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
    
    <!-- Available Keys Grid -->
    <div class="xl:col-span-2 glass-card rounded-2xl p-6 border border-brand-border/60">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-cart-arrow-down text-brand-primary"></i>
            <span>Available Keys for Purchase</span>
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (empty($availableKeys)): ?>
                <div class="col-span-2 text-center text-brand-muted py-12">
                    <i class="fa-solid fa-box-open text-4xl mb-2 opacity-50 block"></i>
                    No keys are currently available for the selected mod.
                </div>
            <?php else: ?>
                <?php foreach ($availableKeys as $key): ?>
                    <div class="bg-brand-bg/50 border border-brand-border/80 rounded-xl p-5 hover:border-brand-primary/50 transition-all duration-300 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-brand-primary/10 text-brand-primary">
                                    <?php echo $key['duration'] . ' ' . $key['duration_type']; ?>
                                </span>
                                <span class="text-sm font-bold text-brand-success"><?php echo formatCurrency($key['price']); ?></span>
                            </div>
                            <h4 class="text-brand-text font-bold text-sm mb-1"><?php echo htmlspecialchars($key['mod_name']); ?></h4>
                            <p class="text-[10px] text-brand-muted font-mono mb-4">Key ID: #<?php echo $key['id']; ?></p>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="key_id" value="<?php echo $key['id']; ?>">
                            <button type="submit" name="purchase_key" class="w-full py-2 bg-brand-primary text-white rounded-lg text-xs font-bold hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center space-x-1.5 shadow-glow">
                                <i class="fa-solid fa-bag-shopping"></i>
                                <span>Buy License</span>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Purchased Keys List -->
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-vault text-brand-secondary"></i>
            <span>My Purchased Keys</span>
        </h3>
        
        <div class="space-y-4">
            <?php if (empty($purchasedKeys)): ?>
                <div class="text-center text-brand-muted py-8">
                    No keys purchased yet.
                </div>
            <?php else: ?>
                <?php foreach ($purchasedKeys as $key): ?>
                    <div class="bg-brand-bg/40 border border-brand-border/60 rounded-xl p-4 flex flex-col justify-between">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-xs font-bold text-brand-text"><?php echo htmlspecialchars($key['mod_name']); ?></span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-brand-secondary/10 text-brand-secondary">
                                <?php echo $key['duration'] . ' ' . $key['duration_type']; ?>
                            </span>
                        </div>
                        
                        <!-- Key display with copy -->
                        <div class="flex items-center space-x-2 bg-brand-surface border border-brand-border px-3 py-2 rounded-lg mb-2">
                            <span class="font-mono text-xs text-brand-text truncate flex-1"><?php echo htmlspecialchars($key['license_key']); ?></span>
                            <button onclick="copyToClipboard('<?php echo htmlspecialchars($key['license_key']); ?>', this)" class="text-brand-muted hover:text-brand-primary transition-colors p-1">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </div>
                        <div class="text-[9px] text-brand-muted text-right">Bought on: <?php echo formatDate($key['sold_at']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>