<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdmin();

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    $error = 'Database connection failed: ' . $e->getMessage();
    $pdo = null;
}

// Get filter parameters
$filters = [
    'mod_id' => $_GET['mod_id'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];

// Get all mods for filter dropdown
$stmt = $pdo->query("SELECT id, name FROM mods ORDER BY name");
$mods = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Build query with filters
$where = ["1=1"];
$params = [];

if (!empty($filters['mod_id'])) {
    $where[] = "lk.mod_id = ?";
    $params[] = $filters['mod_id'];
}

if (!empty($filters['status'])) {
    $where[] = "lk.status = ?";
    $params[] = $filters['status'];
}

if (!empty($filters['search'])) {
    $where[] = "(lk.license_key LIKE ? OR m.name LIKE ?)";
    $searchTerm = '%' . $filters['search'] . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql = "SELECT lk.*, m.name as mod_name 
        FROM license_keys lk 
        LEFT JOIN mods m ON lk.mod_id = m.id 
        WHERE " . implode(' AND ', $where) . " 
        ORDER BY lk.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$licenseKeys = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "License Key List - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-glow-primary">License Key List</h2>
        <p class="text-sm text-brand-muted mt-1 font-medium">Verify generated keys, check sales status, and search the vault.</p>
    </div>
    <a href="license-add" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-primary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all space-x-1.5 shadow-glow">
        <i class="fa-solid fa-plus text-sm"></i>
        <span>Add License Key</span>
    </a>
</div>

<!-- Search & Filters -->
<div class="glass-card rounded-2xl p-5 border border-brand-border/60 mb-6">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="search" class="block text-[10px] font-bold uppercase text-brand-muted mb-1.5">Search</label>
            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($filters['search']); ?>"
                   class="w-full px-4 py-2 bg-brand-bg/50 border border-brand-border rounded-xl text-xs focus:outline-none focus:border-brand-primary text-brand-text"
                   placeholder="Search keys or mod names...">
        </div>
        
        <div>
            <label for="mod_id" class="block text-[10px] font-bold uppercase text-brand-muted mb-1.5">Filter by Mod</label>
            <select id="mod_id" name="mod_id" class="w-full px-4 py-2 bg-brand-bg/50 border border-brand-border rounded-xl text-xs focus:outline-none focus:border-brand-primary text-brand-text">
                <option value="">All Mods</option>
                <?php foreach ($mods as $mod): ?>
                    <option value="<?php echo $mod['id']; ?>" <?php echo $filters['mod_id'] == $mod['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($mod['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="status" class="block text-[10px] font-bold uppercase text-brand-muted mb-1.5">Status</label>
            <div class="flex items-center space-x-2">
                <select id="status" name="status" class="w-full px-4 py-2 bg-brand-bg/50 border border-brand-border rounded-xl text-xs focus:outline-none focus:border-brand-primary text-brand-text">
                    <option value="">All Keys</option>
                    <option value="available" <?php echo $filters['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="sold" <?php echo $filters['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-brand-primary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all">
                    Apply
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="glass-card rounded-2xl p-6 border border-brand-border/60">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-brand-border/60 text-brand-muted text-xs uppercase font-semibold">
                    <th class="pb-3">Mod Name</th>
                    <th class="pb-3">License Key</th>
                    <th class="pb-3">Duration</th>
                    <th class="pb-3">Price</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3 text-right">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border/40 text-sm">
                <?php if (empty($licenseKeys)): ?>
                <tr>
                    <td colspan="6" class="text-center text-brand-muted py-12">
                        <i class="fa-solid fa-key text-3xl mb-2 opacity-50 block"></i>
                        No license keys matching filters.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($licenseKeys as $key): ?>
                    <tr>
                        <td class="py-3.5 font-semibold text-brand-text"><?php echo htmlspecialchars($key['mod_name']); ?></td>
                        <td class="py-3.5 font-mono text-xs text-brand-text">
                            <div class="flex items-center space-x-2">
                                <span><?php echo htmlspecialchars($key['license_key']); ?></span>
                                <button onclick="copyToClipboard('<?php echo htmlspecialchars($key['license_key']); ?>', this)" class="text-brand-muted hover:text-brand-primary p-0.5 transition-colors">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td class="py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-brand-primary/10 text-brand-primary uppercase">
                                <?php echo $key['duration'] . ' ' . $key['duration_type']; ?>
                            </span>
                        </td>
                        <td class="py-3.5 font-bold text-xs text-brand-success">₹<?php echo number_format($key['price'], 2); ?></td>
                        <td class="py-3.5">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?php echo $key['status'] === 'available' ? 'bg-brand-success/10 text-brand-success' : 'bg-brand-muted/20 text-brand-muted'; ?>">
                                <?php echo ucfirst($key['status']); ?>
                            </span>
                        </td>
                        <td class="py-3.5 text-right text-brand-muted text-xs"><?php echo formatDate($key['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>