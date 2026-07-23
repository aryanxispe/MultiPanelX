<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

try {
    $pdo = getDBConnection();
    
    // Get all user transactions (orders)
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = 'Failed to fetch orders: ' . $e->getMessage();
}

$page_title = "My Orders - " . SITE_NAME;
require_once 'includes/header.php';
?>

<!-- Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-neutral-200">My Orders</h2>
        <p class="text-sm text-gray-500 mt-1 dark:text-neutral-400">View and track the status of your purchases.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl p-4 mb-6 flex items-center space-x-2">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?php echo htmlspecialchars($error); ?></span>
    </div>
<?php endif; ?>

<!-- Orders Table -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-900 dark:border-neutral-700">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-200 text-gray-500 text-xs uppercase font-semibold dark:bg-neutral-800/50 dark:border-neutral-700 dark:text-neutral-500">
                    <th class="px-6 py-4">Order Details</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm dark:divide-neutral-700">
                <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-neutral-500">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fa-solid fa-box-open text-4xl mb-3 opacity-20"></i>
                            <p>You haven't made any orders yet.</p>
                            <a href="keys" class="mt-4 inline-flex items-center text-brand-primary hover:underline font-semibold text-sm">
                                Go to Store <i class="fa-solid fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-neutral-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800 dark:text-neutral-200">
                                <?php echo htmlspecialchars($order['reference'] ?: 'Order'); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-500 dark:text-neutral-400">
                                <?php echo formatDate($order['created_at']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if ($order['status'] === 'pending'): ?>
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-500 border border-yellow-200 dark:border-yellow-800">Pending</span>
                            <?php elseif ($order['status'] === 'approved'): ?>
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-500 border border-green-200 dark:border-green-800">Approved</span>
                            <?php elseif ($order['status'] === 'rejected'): ?>
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-500 border border-red-200 dark:border-red-800">Rejected</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-md text-[11px] font-bold uppercase bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700"><?php echo htmlspecialchars($order['status'] ?: 'Unknown'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-sm <?php echo $order['amount'] < 0 ? 'text-red-500' : 'text-green-500'; ?>">
                            <?php echo formatCurrency(abs($order['amount'])); ?>
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
