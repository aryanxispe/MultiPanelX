<?php
require_once 'config/database.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if user is admin (redirect to admin panel)
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin.php');
    exit();
}

$pdo = getDBConnection();

// Get user data
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user statistics
$userStats = [
    'total_purchases' => 0,
    'total_spent' => 0
];

try {
    $stmt = $pdo->prepare("SELECT 
        COUNT(*) as total_purchases,
        SUM(ABS(amount)) as total_spent
        FROM transactions 
        WHERE user_id = ? AND type = 'purchase' AND status = 'completed'");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $userStats = $stats ?: $userStats;
} catch (Exception $e) {
    error_log("Dashboard stats error: " . $e->getMessage());
}

// Get recent transactions
$recentTransactions = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$userId]);
    $recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Dashboard transactions error: " . $e->getMessage());
}

// Get available mods
$mods = [];
try {
    $stmt = $pdo->query("SELECT * FROM mods WHERE status = 'active' ORDER BY name");
    $mods = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Dashboard mods error: " . $e->getMessage());
}

// Helper functions
function formatCurrency($amount) {
    return '&#8377;' . number_format($amount, 2);
}

function formatDate($date) {
    if (empty($date)) return 'N/A';
    $timestamp = strtotime($date);
    if ($timestamp === false) return 'Invalid Date';
    return date('d M Y H:i', $timestamp);
}

// Build last 7 days template for real analytics data
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $dateStr = date('Y-m-d', strtotime("-$i days"));
    $labelStr = date('D d', strtotime("-$i days"));
    $chartData[$dateStr] = [
        'label' => $labelStr,
        'sales_count' => 0,
        'revenue' => 0
    ];
}

// Fetch sales counts (sold keys) for this user
try {
    $stmt = $pdo->prepare("SELECT DATE(sold_at) as date_grp, COUNT(*) as count 
                           FROM license_keys 
                           WHERE status = 'sold' AND sold_to = ? AND sold_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                           GROUP BY DATE(sold_at)");
    $stmt->execute([$userId]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dVal = $row['date_grp'];
        if (isset($chartData[$dVal])) {
            $chartData[$dVal]['sales_count'] = (int)$row['count'];
        }
    }
} catch (Exception $e) {
    // Fail silently
}

// Fetch spending (purchases) from transactions for this user
try {
    $stmt = $pdo->prepare("SELECT DATE(created_at) as date_grp, SUM(ABS(amount)) as total 
                           FROM transactions 
                           WHERE user_id = ? AND type = 'purchase' AND status = 'completed' AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
                           GROUP BY DATE(created_at)");
    $stmt->execute([$userId]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $dVal = $row['date_grp'];
        if (isset($chartData[$dVal])) {
            $chartData[$dVal]['revenue'] = (float)$row['total'];
        }
    }
} catch (Exception $e) {
    // Fail silently
}

// Format arrays for Javascript Chart
$labelsArr = [];
$salesCountArr = [];
$revenueArr = [];
foreach ($chartData as $dateKey => $data) {
    $labelsArr[] = $data['label'];
    $salesCountArr[] = $data['sales_count'];
    $revenueArr[] = $data['revenue'];
}

$page_title = "User Dashboard - " . SITE_NAME . " " . SITE_SUBTITLE;
require_once 'includes/header.php';
?>

<!-- Dashboard Header -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-neutral-200">Dashboard Overview</h2>
        <p class="text-sm text-gray-600 dark:text-neutral-400 mt-1">Welcome back! Here's what's happening with your account.</p>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
    <!-- Stat Card 1 -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 dark:bg-neutral-900 dark:border-neutral-700 flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-brand-secondary/10 flex items-center justify-center text-brand-secondary">
            <i class="fa-solid fa-shopping-cart text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-500">Total Purchases</p>
            <h3 class="text-2xl font-bold mt-1 text-gray-800 dark:text-neutral-200"><?php echo $userStats['total_purchases'] ?: 0; ?></h3>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 dark:bg-neutral-900 dark:border-neutral-700 flex items-center space-x-4">
        <div class="w-12 h-12 rounded-xl bg-brand-success/10 flex items-center justify-center text-brand-success">
            <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
        </div>
        <div>
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider dark:text-neutral-500">Total Spent</p>
            <h3 class="text-2xl font-bold mt-1 text-gray-800 dark:text-neutral-200"><?php echo formatCurrency($userStats['total_spent'] ?: 0); ?></h3>
        </div>
    </div>
</div>

<!-- Analytics Chart Card -->
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-8 dark:bg-neutral-900 dark:border-neutral-700">
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-brand-primary/10 flex items-center justify-center text-brand-primary">
                <i class="fa-solid fa-chart-line text-sm"></i>
            </div>
            <div>
                <h4 class="text-base font-bold text-gray-800 dark:text-neutral-200">My Purchase Analytics</h4>
                <p class="text-[11px] text-gray-500 font-medium mt-0.5 dark:text-neutral-500">Purchases & spending over the last 7 days</p>
            </div>
        </div>
        <div class="flex items-center space-x-4 text-xs font-semibold">
            <span class="flex items-center space-x-1.5 text-brand-primary"><span class="w-2.5 h-2.5 rounded-full bg-brand-primary"></span><span>Keys Purchased</span></span>
            <span class="flex items-center space-x-1.5 text-brand-secondary"><span class="w-2.5 h-2.5 rounded-full bg-brand-secondary"></span><span>Total Spent (&#8377;)</span></span>
        </div>
    </div>
    <div class="h-[240px] w-full relative">
        <canvas id="analyticsChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    
    const labels = <?php echo json_encode($labelsArr); ?>;
    const validationsData = <?php echo json_encode($salesCountArr); ?>;
    const salesData = <?php echo json_encode($revenueArr); ?>;

    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#9ca3af' : '#475569';
    const gridColor = isDark ? 'rgba(31, 41, 55, 0.4)' : 'rgba(226, 232, 240, 0.8)';
    
    const primaryGradient = ctx.createLinearGradient(0, 0, 0, 240);
    primaryGradient.addColorStop(0, 'rgba(99, 102, 241, 0.2)');
    primaryGradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    const secondaryGradient = ctx.createLinearGradient(0, 0, 0, 240);
    secondaryGradient.addColorStop(0, 'rgba(6, 182, 212, 0.2)');
    secondaryGradient.addColorStop(1, 'rgba(6, 182, 212, 0)');

    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Validations',
                    data: validationsData,
                    borderColor: '#6366f1',
                    borderWidth: 3,
                    backgroundColor: primaryGradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#6366f1',
                    pointHoverRadius: 7
                },
                {
                    label: 'Key Sales',
                    data: salesData,
                    borderColor: '#06b6d4',
                    borderWidth: 3,
                    backgroundColor: secondaryGradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#06b6d4',
                    pointHoverRadius: 7
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor,
                        font: {
                            family: 'Inter',
                            size: 11
                        }
                    }
                },
                y: {
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        font: {
                            family: 'Inter',
                            size: 11
                        }
                    }
                }
            }
        }
    });

    window.addEventListener('click', function(e) {
        setTimeout(() => {
            const dark = document.documentElement.classList.contains('dark');
            myChart.options.scales.x.ticks.color = dark ? '#9ca3af' : '#475569';
            myChart.options.scales.y.ticks.color = dark ? '#9ca3af' : '#475569';
            myChart.options.scales.y.grid.color = dark ? 'rgba(31, 41, 55, 0.4)' : 'rgba(226, 232, 240, 0.8)';
            myChart.update();
        }, 50);
    });
});
</script>

<!-- Grid Layout for Content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Available Mods Section -->
    <div class="lg:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm p-6 dark:bg-neutral-900 dark:border-neutral-700">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-8 h-8 rounded-lg bg-brand-primary/10 flex items-center justify-center text-brand-primary">
                <i class="fa-solid fa-gamepad text-sm"></i>
            </div>
            <h4 class="text-base font-bold text-gray-800 dark:text-neutral-200">Available Mod Packages</h4>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (empty($mods)): ?>
                <div class="col-span-2 text-center text-gray-500 py-8 dark:text-neutral-500">
                    <i class="fa-solid fa-box-open text-3xl mb-2 opacity-50 block"></i>
                    No mod packages are currently active.
                </div>
            <?php else: ?>
                <?php foreach ($mods as $mod): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 hover:border-brand-primary/50 transition-all duration-300 flex flex-col justify-between dark:bg-neutral-800 dark:border-neutral-700">
                    <div>
                        <h5 class="text-brand-primary font-bold text-sm mb-1.5"><?php echo htmlspecialchars($mod['name']); ?></h5>
                        <p class="text-xs text-gray-600 leading-relaxed mb-4 dark:text-neutral-400"><?php echo htmlspecialchars($mod['description'] ?: 'No description available'); ?></p>
                    </div>
                    <a href="keys?mod_id=<?php echo $mod['id']; ?>" class="inline-flex items-center justify-center px-4 py-2 bg-brand-primary/10 hover:bg-brand-primary/20 text-brand-primary text-xs font-bold rounded-lg transition-colors space-x-1.5 w-max">
                        <i class="fa-solid fa-key"></i>
                        <span>Get Keys</span>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Transactions Section -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 dark:bg-neutral-900 dark:border-neutral-700">
        <div class="flex items-center space-x-3 mb-6">
            <div class="w-8 h-8 rounded-lg bg-brand-secondary/10 flex items-center justify-center text-brand-secondary">
                <i class="fa-solid fa-shopping-cart text-sm"></i>
            </div>
            <h4 class="text-base font-bold text-gray-800 dark:text-neutral-200">My Orders</h4>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 text-gray-500 text-xs uppercase font-semibold dark:border-neutral-700 dark:text-neutral-500">
                        <th class="pb-3">Order Details</th>
                        <th class="pb-3 text-center">Status</th>
                        <th class="pb-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm dark:divide-neutral-700">
                    <?php if (empty($recentTransactions)): ?>
                    <tr>
                        <td colspan="3" class="text-center text-gray-500 py-8 dark:text-neutral-500">
                            No orders yet
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recentTransactions as $transaction): ?>
                        <tr>
                            <td class="py-3">
                                <div class="text-xs font-bold text-gray-800 dark:text-neutral-200">
                                    <?php echo htmlspecialchars($transaction['reference'] ?: 'Order'); ?>
                                </div>
                                <div class="text-[10px] text-gray-500 mt-1 dark:text-neutral-500"><?php echo formatDate($transaction['created_at']); ?></div>
                            </td>
                            <td class="py-3 text-center">
                                <?php if ($transaction['status'] === 'pending'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-500">Pending</span>
                                <?php elseif ($transaction['status'] === 'approved'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-500">Approved</span>
                                <?php elseif ($transaction['status'] === 'rejected'): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-500">Rejected</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400"><?php echo htmlspecialchars($transaction['status'] ?: 'Unknown'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 text-right font-bold text-xs <?php echo $transaction['amount'] < 0 ? 'text-red-500' : 'text-green-500'; ?>">
                                <?php echo formatCurrency(abs($transaction['amount'])); ?>
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


