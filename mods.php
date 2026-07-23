<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdmin();

$pdo = getDBConnection();

$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $modId = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT image_url FROM mods WHERE id = ?");
    $stmt->execute([$modId]);
    $mod = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("DELETE FROM mods WHERE id = ?");
    if ($stmt->execute([$modId])) {
        if ($mod && $mod['image_url'] && file_exists($mod['image_url'])) {
            unlink($mod['image_url']);
        }
        $success = 'Mod deleted successfully!';
    } else {
        $error = 'Failed to delete mod.';
    }
}

// Handle status toggle
if (isset($_GET['toggle_status']) && is_numeric($_GET['toggle_status'])) {
    $stmt = $pdo->prepare("UPDATE mods SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ?");
    if ($stmt->execute([$_GET['toggle_status']])) {
        $success = 'Mod status updated successfully!';
    } else {
        $error = 'Failed to update mod status.';
    }
}

// Get all mods
$stmt = $pdo->query("SELECT * FROM mods ORDER BY created_at DESC");
$mods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Manage Mods - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-glow-primary">Manage Mods</h2>
        <p class="text-sm text-brand-muted mt-1">View and manage all mod entries in the system.</p>
    </div>
    <div class="flex items-center space-x-3">
        <a href="upload" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-secondary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all space-x-1.5 shadow-glow-cyan">
            <i class="fa-solid fa-cloud-arrow-up text-sm"></i>
            <span>Upload APK</span>
        </a>
        <a href="mod-add" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-primary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all space-x-1.5 shadow-glow">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>Add New Mod</span>
        </a>
    </div>
</div>

<!-- Alerts -->
<?php if ($success): ?>
    <div class="bg-brand-success/10 border border-brand-success/20 text-brand-success text-xs rounded-xl p-3.5 mb-6 flex items-center space-x-2.5">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span><?php echo htmlspecialchars($success); ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="bg-brand-error/10 border border-brand-error/20 text-brand-error text-xs rounded-xl p-3.5 mb-6 flex items-center space-x-2.5">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<!-- Mod List Card -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 dark:bg-neutral-900 dark:border-neutral-700">
    <div class="flex items-center justify-between mb-6">
        <h4 class="text-base font-bold flex items-center space-x-2">
            <i class="fa-solid fa-gamepad text-brand-primary"></i>
            <span>Active Mods</span>
        </h4>
        <span class="text-xs text-brand-muted font-semibold">Total Mods: <?php echo count($mods); ?></span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-brand-border/60 text-brand-muted text-xs uppercase font-semibold">
                    <th class="pb-3" style="width: 80px;">ID</th>
                    <th class="pb-3">Name</th>
                    <th class="pb-3 hidden md:table-cell">Description</th>
                    <th class="pb-3" style="width: 120px;">Status</th>
                    <th class="pb-3 text-right" style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border/40 text-sm">
                <?php if (empty($mods)): ?>
                <tr>
                    <td colspan="5" class="text-center text-brand-muted py-12">
                        <i class="fa-solid fa-box-open text-3xl mb-2 opacity-50 block"></i>
                        No mods found. Get started by adding one!
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($mods as $mod): ?>
                    <tr>
                        <td class="py-3.5 font-mono text-xs text-brand-muted">#<?php echo $mod['id']; ?></td>
                        <td class="py-3.5 font-semibold text-brand-text">
                            <?php echo htmlspecialchars($mod['name']); ?>
                            <div class="md:hidden mt-1">
                                <p class="text-[10px] text-brand-muted font-normal leading-relaxed"><?php echo htmlspecialchars($mod['description'] ?: 'No description'); ?></p>
                            </div>
                        </td>
                        <td class="py-3.5 hidden md:table-cell text-brand-muted text-xs leading-relaxed max-w-xs truncate"><?php echo htmlspecialchars($mod['description'] ?: 'No description provided'); ?></td>
                        <td class="py-3.5">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase <?php echo $mod['status'] === 'active' ? 'bg-brand-success/10 text-brand-success' : 'bg-brand-muted/20 text-brand-muted'; ?>">
                                <?php echo ucfirst($mod['status']); ?>
                            </span>
                        </td>
                        <td class="py-3.5 text-right">
                            <div class="inline-flex space-x-2">
                                <a href="mod-edit.php?id=<?php echo $mod['id']; ?>" 
                                   title="Edit Mod"
                                   class="p-2 rounded-lg bg-brand-surface border border-brand-border text-xs font-bold hover:text-brand-info hover:border-brand-info/40 transition-colors">
                                    <i class="fa-solid fa-pen text-brand-info"></i>
                                </a>
                                <a href="?toggle_status=<?php echo $mod['id']; ?>" 
                                   title="<?php echo $mod['status'] === 'active' ? 'Pause Mod' : 'Activate Mod'; ?>"
                                   class="p-2 rounded-lg bg-brand-surface border border-brand-border text-xs font-bold hover:text-brand-primary hover:border-brand-primary/40 transition-colors"
                                   onclick="return confirm('Change status of this mod?')">
                                    <i class="fa-solid <?php echo $mod['status'] === 'active' ? 'fa-pause text-brand-warning' : 'fa-play text-brand-success'; ?>"></i>
                                </a>
                                <a href="?delete=<?php echo $mod['id']; ?>" 
                                   title="Delete Mod"
                                   class="p-2 rounded-lg bg-brand-surface border border-brand-border text-xs font-bold hover:text-brand-error hover:border-brand-error/40 transition-colors"
                                   onclick="return confirm('Are you sure you want to delete this mod?')">
                                    <i class="fa-regular fa-trash-can text-brand-error"></i>
                                </a>
                            </div>
                        </td>
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
