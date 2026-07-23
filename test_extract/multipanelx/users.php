<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireAdmin();

$success = '';
$error = '';

try {
    $pdo = getDBConnection();
} catch (Exception $e) {
    $error = 'Database connection failed: ' . $e->getMessage();
    $pdo = null;
}

// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete']) && $pdo) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
        if ($stmt->execute([$_GET['delete']])) {
            $success = 'User deleted successfully!';
        } else {
            $error = 'Failed to delete user.';
        }
    } catch (Exception $e) {
        $error = 'Error deleting user: ' . $e->getMessage();
    }
}

// Get all users
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $users = [];
    }
} catch (Exception $e) {
    $users = [];
    $error = 'Failed to fetch users: ' . $e->getMessage();
}

// Get user statistics
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total_users,
            SUM(balance) as total_balance,
            COUNT(CASE WHEN balance > 0 THEN 1 END) as users_with_balance,
            AVG(balance) as avg_balance
            FROM users WHERE role = 'user'");
        $userStats = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $userStats = [
            'total_users' => 0,
            'total_balance' => 0,
            'users_with_balance' => 0,
            'avg_balance' => 0
        ];
    }
} catch (Exception $e) {
    $userStats = [
        'total_users' => 0,
        'total_balance' => 0,
        'users_with_balance' => 0,
        'avg_balance' => 0
    ];
}

$page_title = "Manage Users - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-glow-primary">Manage Users</h2>
        <p class="text-sm text-brand-muted mt-1 font-medium">Add, remove, and manage user accounts and wallets.</p>
    </div>
    <a href="balance-add" class="inline-flex items-center justify-center px-4 py-2.5 bg-brand-primary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all space-x-1.5 shadow-glow">
        <i class="fa-solid fa-wallet text-sm"></i>
        <span>Add Balance</span>
    </a>
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

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="glass-card rounded-2xl p-6 border border-brand-border/60 flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary shadow-glow">
            <i class="fa-solid fa-users text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-brand-muted uppercase tracking-wider">Total Users</p>
            <h3 class="text-xl font-bold mt-1 text-brand-text"><?php echo $userStats['total_users'] ?: 0; ?></h3>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-brand-border/60 flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-brand-secondary/10 border border-brand-secondary/20 flex items-center justify-center text-brand-secondary shadow-glow-cyan">
            <i class="fa-solid fa-indian-rupee-sign text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-brand-muted uppercase tracking-wider">Total User Balances</p>
            <h3 class="text-xl font-bold mt-1 text-brand-text">₹<?php echo number_format((float)$userStats['total_balance'], 2); ?></h3>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-brand-border/60 flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-brand-success/10 border border-brand-success/20 flex items-center justify-center text-brand-success">
            <i class="fa-solid fa-coins text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-brand-muted uppercase tracking-wider">Funded Wallets</p>
            <h3 class="text-xl font-bold mt-1 text-brand-text"><?php echo $userStats['users_with_balance'] ?: 0; ?></h3>
        </div>
    </div>

    <div class="glass-card rounded-2xl p-6 border border-brand-border/60 flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-brand-warning/10 border border-brand-warning/20 flex items-center justify-center text-brand-warning">
            <i class="fa-solid fa-calculator text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-brand-muted uppercase tracking-wider">Average Wallet</p>
            <h3 class="text-xl font-bold mt-1 text-brand-text">₹<?php echo number_format((float)$userStats['avg_balance'], 2); ?></h3>
        </div>
    </div>
</div>

<!-- Users List Card -->
<div class="glass-card rounded-2xl p-6 border border-brand-border/60">
    <div class="flex items-center space-x-3 mb-6">
        <div class="w-8 h-8 rounded-lg bg-brand-primary/10 flex items-center justify-center text-brand-primary">
            <i class="fa-solid fa-circle-user text-sm"></i>
        </div>
        <h4 class="text-base font-bold">Registered Users</h4>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-brand-border/60 text-brand-muted text-xs uppercase font-semibold">
                    <th class="pb-3">Username</th>
                    <th class="pb-3">Email</th>
                    <th class="pb-3">Referral Code</th>
                    <th class="pb-3">Joined</th>
                    <th class="pb-3">Wallet Balance</th>
                    <th class="pb-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-border/40 text-sm">
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" class="text-center text-brand-muted py-12">
                        <i class="fa-solid fa-user-slash text-3xl mb-2 opacity-50 block"></i>
                        No users registered yet.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="py-3.5 flex items-center space-x-3">
                            <div class="w-8 h-8 rounded-full bg-brand-primary/20 border border-brand-primary/30 flex items-center justify-center text-xs font-bold text-brand-primary uppercase">
                                <?php echo substr($u['username'], 0, 2); ?>
                            </div>
                            <span class="font-semibold text-brand-text"><?php echo htmlspecialchars($u['username']); ?></span>
                        </td>
                        <td class="py-3.5 text-brand-muted text-xs"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td class="py-3.5 font-mono text-xs text-brand-text">
                            <?php echo $u['referral_code'] ? htmlspecialchars($u['referral_code']) : 'None'; ?>
                        </td>
                        <td class="py-3.5 text-brand-muted text-xs"><?php echo date('d M Y', strtotime($u['created_at'])); ?></td>
                        <td class="py-3.5 font-bold text-xs text-brand-success">₹<?php echo number_format($u['balance'], 2); ?></td>
                        <td class="py-3.5 text-right">
                            <a href="?delete=<?php echo $u['id']; ?>" 
                               class="p-2 rounded-lg bg-brand-surface border border-brand-border text-xs font-bold text-brand-error hover:border-brand-error/40 transition-colors inline-block"
                               onclick="return confirm('Are you sure you want to delete this user? All their transactions and purchased keys will be permanently deleted.')">
                                <i class="fa-regular fa-trash-can text-brand-error"></i>
                            </a>
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