<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pdo = getDBConnection();
$userId = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Helper functions


$page_title = "My Balance - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8">
    <h2 class="text-2xl font-bold tracking-tight text-glow-primary">Wallet & Balance</h2>
    <p class="text-sm text-brand-muted mt-1">Check your current balance and view your complete financial history.</p>
</div>

<!-- Grid Layout -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    
    <!-- Balance Card and Deposit Info -->
    <div class="space-y-6">
        <!-- Balance Widget -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 text-center relative overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
            <!-- Glow effect -->
            <div class="absolute -top-12 -left-12 w-24 h-24 rounded-full bg-brand-primary/10 blur-xl"></div>
            
            <div class="w-16 h-16 rounded-2xl bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary shadow-glow mx-auto mb-4">
                <i class="fa-solid fa-wallet text-2xl"></i>
            </div>
            <h4 class="text-xs font-semibold text-brand-muted uppercase tracking-wider mb-1">Available Balance</h4>
            <h2 class="text-3xl font-black text-brand-text text-glow-primary mb-2"><?php echo formatCurrency($user['balance']); ?></h2>
            <p class="text-[10px] text-brand-muted leading-relaxed">Use this balance to purchase license keys instantly from the store.</p>
        </div>

        <!-- Add Balance Info -->
        <div class="bg-brand-surface/40 border border-brand-border rounded-2xl p-6">
            <h4 class="text-sm font-bold text-brand-text mb-3 flex items-center space-x-2">
                <i class="fa-solid fa-circle-info text-brand-secondary"></i>
                <span>How to Add Balance?</span>
            </h4>
            <p class="text-xs text-brand-muted leading-relaxed mb-4">
                To top up your wallet balance, please contact the administrator directly. Provide your registered username and payment receipt.
            </p>
            <div class="p-3.5 bg-brand-bg/50 border border-brand-border rounded-xl flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-brand-secondary/15 flex items-center justify-center text-brand-secondary">
                    <i class="fa-regular fa-envelope text-sm"></i>
                </div>
                <div class="truncate">
                    <p class="text-[10px] text-brand-muted uppercase tracking-wider font-bold">Admin Support</p>
                    <a href="mailto:<?php echo htmlspecialchars(SUPPORT_EMAIL); ?>" class="text-xs font-bold text-brand-secondary hover:underline truncate block"><?php echo htmlspecialchars(SUPPORT_EMAIL); ?></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Card -->
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm p-6 dark:bg-neutral-900 dark:border-neutral-700">
        <h3 class="text-base font-bold mb-4 flex items-center space-x-2">
            <i class="fa-solid fa-receipt text-brand-primary"></i>
            <span>Transaction History</span>
        </h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-brand-border/60 text-brand-muted text-xs uppercase font-semibold">
                        <th class="pb-3">Transaction ID</th>
                        <th class="pb-3">Type</th>
                        <th class="pb-3">Reference / Details</th>
                        <th class="pb-3">Date</th>
                        <th class="pb-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/40 text-sm">
                    <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-brand-muted py-12">
                            <i class="fa-solid fa-receipt text-3xl mb-2 opacity-50 block"></i>
                            No transaction history available.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td class="py-3.5 font-mono text-xs text-brand-muted">#<?php echo $tx['id']; ?></td>
                            <td class="py-3.5">
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase <?php echo $tx['type'] === 'purchase' ? 'bg-brand-primary/10 text-brand-primary' : 'bg-brand-success/10 text-brand-success'; ?>">
                                    <?php echo htmlspecialchars($tx['type']); ?>
                                </span>
                            </td>
                            <td class="py-3.5 text-xs text-brand-text font-medium"><?php echo htmlspecialchars($tx['reference'] ?: 'Top-up Balance'); ?></td>
                            <td class="py-3.5 text-xs text-brand-muted"><?php echo formatDate($tx['created_at']); ?></td>
                            <td class="py-3.5 text-right font-bold text-xs <?php echo $tx['amount'] < 0 ? 'text-brand-error' : 'text-brand-success'; ?>">
                                <?php echo $tx['amount'] < 0 ? '-' . formatCurrency(abs($tx['amount'])) : '+' . formatCurrency($tx['amount']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>
