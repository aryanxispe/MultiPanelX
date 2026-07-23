<?php
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

// Handle plan purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_plan'])) {
    $planId = (int)($_POST['plan_id'] ?? 0);
    $txnId = trim($_POST['upi_txn_id'] ?? '');
    
    try {
        $pdo->beginTransaction();
        
        // Get plan details
        $stmt = $pdo->prepare("SELECT p.*, m.name as mod_name FROM mod_plans p 
                              LEFT JOIN mods m ON p.mod_id = m.id 
                              WHERE p.id = ? AND p.status = 'active'");
        $stmt->execute([$planId]);
        $plan = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$plan) {
            throw new Exception("Plan not available");
        }
        
        // Add pending transaction with UPI txn ID
        $reference = "Plan purchase: " . $plan['mod_name'] . " - " . $plan['plan_name'];
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, type, reference, status, plan_id, upi_txn_id) VALUES (?, ?, 'purchase', ?, 'pending', ?, ?)");
        $stmt->execute([$userId, $plan['price'], $reference, $plan['id'], $txnId ?: null]);
        
        $pdo->commit();
        $success = 'Order placed! Admin will verify your UPI payment and approve shortly.';
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}

// Get filter parameters
$modId = $_GET['mod_id'] ?? '';

// Read UPI ID for QR Generation
$adminUpiId = '';
if (file_exists('config/upi.json')) {
    $upiData = json_decode(file_get_contents('config/upi.json'), true);
    if ($upiData && isset($upiData['upi_id'])) {
        $adminUpiId = $upiData['upi_id'];
    }
}

// Get all mods for filter dropdown
$stmt = $pdo->query("SELECT * FROM mods WHERE status = 'active' ORDER BY name");
$mods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available plans
$where = "p.status = 'active'";
$params = [];

if ($modId) {
    $where .= " AND p.mod_id = ?";
    $params[] = $modId;
}

$sql = "SELECT p.*, m.name as mod_name, m.image_url, m.features 
        FROM mod_plans p 
        LEFT JOIN mods m ON p.mod_id = m.id 
        WHERE $where 
        ORDER BY m.name ASC, p.price ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$availablePlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's purchased keys
$stmt = $pdo->prepare("SELECT lk.*, m.name as mod_name, m.image_url 
                      FROM license_keys lk 
                      LEFT JOIN mods m ON lk.mod_id = m.id 
                      WHERE lk.sold_to = ? AND lk.sold_at IS NOT NULL 
                      ORDER BY lk.sold_at DESC");
$stmt->execute([$userId]);
$purchasedKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functions
function formatCurrency(float $amount) {
    return '&#8377;' . number_format($amount, 2);
}

function formatDate(string $date) {
    return date('d M Y H:i', strtotime($date));
}

$page_title = "Store - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-glow-primary">Store</h2>
        <p class="text-sm text-brand-muted mt-1">Browse available plans and manage your purchased licenses.</p>
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

<!-- Available Plans -->
<div class="mb-12">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-bold text-brand-text flex items-center">
            <i class="fa-solid fa-tags text-brand-primary mr-2"></i>
            Available Plans
        </h3>
        <span class="text-xs font-bold text-brand-muted bg-brand-surface px-3 py-1 rounded-full border border-brand-border">
            <?php echo count($availablePlans); ?> Plans
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php if (empty($availablePlans)): ?>
            <div class="col-span-full bg-brand-surface border border-brand-border rounded-2xl p-12 text-center">
                <i class="fa-solid fa-box-open text-4xl mb-3 text-brand-primary opacity-50 block"></i>
                <h4 class="text-base font-bold text-brand-text mb-1">No Plans Available</h4>
                <p class="text-xs text-brand-muted max-w-sm mx-auto">There are currently no license plans available for purchase in the store.</p>
            </div>
        <?php else: ?>
            <?php foreach ($availablePlans as $plan): ?>
                <div class="bg-brand-surface border border-brand-border rounded-2xl overflow-hidden shadow-sm flex flex-col group hover:border-brand-primary/30 transition-all duration-300">
                    <!-- Card Header -->
                    <div class="h-48 bg-brand-bg/80 relative flex items-center justify-center overflow-hidden">
                        <?php if ($plan['image_url'] && file_exists($plan['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($plan['image_url']); ?>" alt="<?php echo htmlspecialchars($plan['mod_name']); ?>" class="w-full h-full object-contain filter drop-shadow-2xl group-hover:scale-110 transition-transform duration-500">
                        <?php else: ?>
                            <i class="fa-solid fa-gamepad text-5xl text-brand-muted/30 group-hover:text-brand-primary/40 transition-colors"></i>
                        <?php endif; ?>
                        
                        <div class="absolute top-3 right-3 bg-brand-primary/20 backdrop-blur-md px-2.5 py-1 rounded-lg border border-brand-primary/30 flex items-center gap-1.5 shadow-xl">
                            <span class="text-[10px] font-bold text-brand-primary uppercase tracking-wider"><?php echo htmlspecialchars($plan['plan_name']); ?></span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="mb-4">
                            <h4 class="text-base font-bold text-brand-text group-hover:text-brand-primary transition-colors"><?php echo htmlspecialchars($plan['mod_name']); ?></h4>
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="px-2 py-0.5 bg-brand-primary/10 border border-brand-primary/20 text-brand-primary text-[10px] font-bold rounded uppercase tracking-wider">
                                    <?php echo $plan['duration'] . ' ' . ucfirst($plan['duration_type']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if ($plan['features']): ?>
                        <div class="mb-5 space-y-2 flex-1">
                            <?php 
                            $features = explode("\n", $plan['features']);
                            $features = array_slice($features, 0, 3);
                            foreach ($features as $feature): 
                                $feature = trim($feature);
                                if (empty($feature)) continue;
                            ?>
                            <div class="flex items-start gap-2 text-xs text-brand-muted">
                                <i class="fa-solid fa-check text-brand-success mt-0.5 text-[10px]"></i>
                                <span class="line-clamp-1 leading-relaxed"><?php echo htmlspecialchars($feature); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                            <div class="flex-1"></div>
                        <?php endif; ?>

                        <!-- Action -->
                        <div class="pt-4 border-t border-brand-border flex items-center justify-between mt-auto">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-brand-muted font-bold uppercase tracking-wider mb-0.5">Price</span>
                                <span class="text-lg font-bold text-glow-primary"><?php echo formatCurrency($plan['price']); ?></span>
                            </div>
                            <button type="button" onclick="openPurchaseModal(<?php echo htmlspecialchars(json_encode($plan)); ?>)" class="h-10 px-5 bg-brand-primary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-95 transition-all flex items-center justify-center gap-2 shadow-glow">
                                <i class="fa-solid fa-cart-shopping"></i>
                                <span>Buy Now</span>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- My Purchased Keys -->
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
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($purchasedKeys as $key): ?>
                <div class="bg-brand-bg/40 border border-brand-border/60 rounded-xl p-4 flex flex-col justify-between">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-brand-text"><?php echo htmlspecialchars($key['mod_name']); ?></span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-brand-secondary/10 text-brand-secondary">
                            <?php echo $key['duration'] . ' ' . $key['duration_type']; ?>
                        </span>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-brand-surface border border-brand-border px-3 py-2 rounded-lg mb-2">
                        <span class="font-mono text-xs text-brand-text truncate flex-1"><?php echo htmlspecialchars($key['license_key']); ?></span>
                        <button onclick="copyToClipboard('<?php echo htmlspecialchars($key['license_key']); ?>', this)" class="text-brand-muted hover:text-brand-primary transition-colors p-1">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <div class="text-[9px] text-brand-muted text-right">Bought on: <?php echo formatDate($key['sold_at']); ?></div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Purchase Modal -->
<div id="purchaseModal" class="fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm px-4" style="display:none">
    <div class="bg-brand-surface border border-brand-border rounded-2xl w-full max-w-md p-6 shadow-2xl relative animate-fade-in mx-auto">
        <button type="button" onclick="closePurchaseModal()" class="absolute top-4 right-4 text-brand-muted hover:text-brand-text transition-colors w-8 h-8 flex items-center justify-center rounded-lg hover:bg-brand-bg">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
        
        <div class="text-center mb-6">
            <h3 class="text-xl font-bold text-glow-primary mb-2">Complete Purchase</h3>
            <p class="text-sm text-brand-muted">Scan the QR code below and pay the exact amount.</p>
        </div>

        <div class="bg-brand-bg/50 rounded-xl p-4 mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-brand-muted">Plan:</span>
                <span class="text-sm font-bold text-brand-text" id="modalPlanName">-</span>
            </div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-xs font-bold text-brand-muted">Duration:</span>
                <span class="text-sm font-bold text-brand-primary" id="modalPlanDuration">-</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs font-bold text-brand-muted">Amount to Pay:</span>
                <span class="text-lg font-bold text-brand-success" id="modalPlanPrice">-</span>
            </div>
        </div>
        
        <?php if ($adminUpiId): ?>
            <div class="flex justify-center mb-6 bg-white p-4 rounded-xl mx-auto w-fit">
                <img id="qrCodeImg" src="" alt="UPI QR Code" class="w-48 h-48">
            </div>
            <div class="text-center mb-6">
                <p class="text-xs text-brand-muted mb-1">Or pay to UPI ID:</p>
                <div class="flex items-center justify-center gap-2">
                    <span class="font-mono text-sm font-bold text-brand-text"><?php echo htmlspecialchars($adminUpiId); ?></span>
                    <button onclick="copyToClipboard('<?php echo htmlspecialchars($adminUpiId); ?>', this)" class="text-brand-primary hover:text-white transition-colors">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center text-brand-warning bg-brand-warning/10 p-4 rounded-xl mb-6">
                <i class="fa-solid fa-triangle-exclamation mb-2 text-xl"></i>
                <p class="text-sm font-bold">Admin has not set a UPI ID.</p>
                <p class="text-xs mt-1">Please contact admin to complete your purchase.</p>
            </div>
        <?php endif; ?>

        <form method="POST" id="purchaseForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="plan_id" id="modalPlanId" value="">
            <div class="mb-4">
                <label for="upi_txn_id" class="block text-xs font-semibold text-brand-muted mb-1.5">UPI Transaction ID <span class="text-brand-muted">(required)</span></label>
                <input type="text" id="upi_txn_id" name="upi_txn_id" required
                       placeholder="e.g. 426781234567"
                       class="w-full px-4 py-2.5 bg-brand-bg/50 border border-brand-border rounded-xl text-sm focus:outline-none focus:border-brand-primary font-mono">
                <p class="text-[10px] text-brand-muted mt-1.5">Enter the 12-digit UPI reference/transaction ID from your payment app.</p>
            </div>
            <button type="submit" name="purchase_plan" class="w-full h-12 bg-brand-primary text-white text-sm font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all flex items-center justify-center gap-2 shadow-glow">
                <i class="fa-solid fa-check-circle"></i>
                <span>I have paid – Submit Order</span>
            </button>
        </form>
    </div>
</div>

<script>
const adminUpiId = <?php echo json_encode($adminUpiId); ?>;

function openPurchaseModal(plan) {
    document.getElementById('modalPlanId').value = plan.id;
    document.getElementById('modalPlanName').textContent = plan.mod_name + ' - ' + plan.plan_name;
    document.getElementById('modalPlanDuration').textContent = plan.duration + ' ' + (plan.duration_type.charAt(0).toUpperCase() + plan.duration_type.slice(1));
    document.getElementById('modalPlanPrice').innerHTML = '&#8377;' + parseFloat(plan.price).toFixed(2);
    
    if (adminUpiId) {
        const upiString = 'upi://pay?pa=' + adminUpiId + '&pn=Admin&am=' + plan.price + '&cu=INR';
        document.getElementById('qrCodeImg').src = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(upiString);
    }
    
    const modal = document.getElementById('purchaseModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closePurchaseModal() {
    const modal = document.getElementById('purchaseModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// Close modal when clicking outside
document.getElementById('purchaseModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePurchaseModal();
    }
});

function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        icon.className = 'fa-solid fa-check text-brand-success';
        setTimeout(() => {
            icon.className = 'fa-regular fa-copy';
        }, 2000);
    });
}
</script>

<?php
require_once 'includes/footer.php';
?>



