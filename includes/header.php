<?php
require_once 'includes/auth.php';
$isLoggedIn = isLoggedIn();
$isAdmin = $isLoggedIn && isAdmin();
$current_page = basename($_SERVER['PHP_SELF']);

if ($isLoggedIn) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $dbBalance = $stmt->fetchColumn();
        if ($dbBalance !== false) {
            $_SESSION['balance'] = (float)$dbBalance;
        }
    } catch (Exception $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : SITE_NAME . ' ' . SITE_SUBTITLE; ?></title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            colors: {
              brand: {
                primary: 'rgb(var(--brand-primary-rgb))',
                secondary: 'rgb(var(--brand-secondary-rgb))',
                bg: 'rgb(var(--brand-bg-rgb))',
                surface: 'rgb(var(--brand-surface-rgb))',
                border: 'rgb(var(--brand-border-rgb))',
                text: 'rgb(var(--brand-text-rgb))',
                muted: 'rgb(var(--brand-muted-rgb))',
                success: '#10b981',
                warning: '#f59e0b',
                error: '#ef4444'
              }
            },
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
            },
            boxShadow: {
              glow: '0 0 15px rgba(99, 102, 241, 0.15)',
              'glow-cyan': '0 0 15px rgba(6, 182, 212, 0.15)'
            }
          }
        }
      }
    </script>
    <style type="text/tailwindcss">
      :root {
        --brand-primary-rgb: 99 102 241;
        --brand-secondary-rgb: 6 182 212;
        --brand-bg-rgb: 226 232 240;
        --brand-surface-rgb: 248 250 252;
        --brand-border-rgb: 148 163 184;
        --brand-text-rgb: 15 23 42;
        --brand-muted-rgb: 71 85 105;
      }

      .dark {
        --brand-bg-rgb: 9 9 11;
        --brand-surface-rgb: 24 24 27;
        --brand-border-rgb: 39 39 42;
        --brand-text-rgb: 250 250 250;
        --brand-muted-rgb: 161 161 170;
      }

      .glass-panel {
        background-color: var(--brand-surface);
        border: 1px solid var(--brand-border);
        backdrop-filter: blur(12px);
      }

      .glass-card {
        background-color: rgb(var(--brand-surface-rgb));
        border: 1px solid rgb(var(--brand-border-rgb));
        backdrop-filter: blur(8px);
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.08);
      }

      .dark .glass-card {
        background-color: rgba(var(--brand-surface-rgb), 0.5);
        border: 1px solid rgba(var(--brand-border-rgb), 0.8);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.5), 0 2px 4px -2px rgba(0, 0, 0, 0.5);
      }

      .text-glow-primary {
        text-shadow: 0 0 10px rgba(99, 102, 241, 0.4);
      }

      .text-glow-cyan {
        text-shadow: 0 0 10px rgba(6, 182, 212, 0.4);
      }

      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .animate-fade-in {
        animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      }

      body {
        background-color: var(--brand-bg);
        color: var(--brand-text);
        font-family: 'Inter', sans-serif;
        min-height: 100vh;
        background-image: 
          radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.03) 0%, transparent 40%),
          radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.03) 0%, transparent 40%);
      }

      .dark body {
        background-image: 
          radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 40%),
          radial-gradient(circle at 90% 80%, rgba(6, 182, 212, 0.05) 0%, transparent 40%);
      }

      /* Dropdown option styles */
      select option {
        background-color: var(--brand-surface);
        color: var(--brand-text);
      }

      /* Custom Scrollbar */
      ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
      }
      ::-webkit-scrollbar-track {
        background-color: var(--brand-bg);
      }
      ::-webkit-scrollbar-thumb {
        background-color: var(--brand-border);
        border-radius: 9999px;
      }
      ::-webkit-scrollbar-thumb:hover {
        background-color: rgba(156, 163, 175, 0.4);
      }
    </style>

</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased dark:bg-neutral-900 dark:text-gray-200 flex flex-col min-h-screen">

<?php if ($isLoggedIn): ?>
    <!-- ========== HEADER ========== -->
    <header class="sticky top-0 inset-x-0 flex flex-wrap sm:justify-start sm:flex-nowrap z-[48] w-full bg-white border-b border-gray-200 text-sm py-4 sm:py-5 lg:ps-64 dark:bg-neutral-900 dark:border-neutral-700">
        <nav class="flex items-center justify-between w-full mx-auto px-4 sm:px-6" aria-label="Global">
            <div class="lg:hidden flex items-center">
                <!-- Logo for mobile -->
                <a class="flex-none text-xl font-semibold dark:text-white flex items-center gap-x-2" href="index.php" aria-label="Brand">
                    <div class="inline-flex w-8 h-8 rounded-lg bg-gradient-to-tr from-brand-primary to-brand-secondary items-center justify-center">
                        <i class="fa-solid fa-cube text-white text-sm"></i>
                    </div>
                    <?php echo SITE_NAME; ?>
                </a>
            </div>
            <!-- Global Search Bar (Desktop mostly) -->
            <div class="hidden lg:flex flex-1 max-w-lg me-auto relative">
                <div class="relative w-full group">
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4">
                        <i class="fa-solid fa-magnifying-glass text-brand-muted group-focus-within:text-brand-primary transition-colors"></i>
                    </div>
                    <input type="text" id="global-search-input" class="block w-full rounded-2xl border border-brand-border bg-brand-surface/40 hover:bg-brand-surface/70 py-2.5 ps-11 pe-14 text-sm text-brand-text placeholder-brand-muted focus:border-brand-primary focus:bg-brand-surface focus:ring-1 focus:ring-brand-primary/50 transition-all shadow-sm" placeholder="Search mods, keys, or users... (Ctrl+K)" autocomplete="off">
                    <div class="absolute inset-y-0 end-0 flex items-center pointer-events-none pe-3">
                        <kbd class="text-[10px] font-semibold text-brand-muted border border-brand-border/50 rounded px-1.5 py-0.5 bg-brand-bg shadow-sm">Ctrl K</kbd>
                    </div>
                </div>
                
                <!-- Search Results Dropdown -->
                <div id="search-dropdown" class="absolute top-full left-0 mt-2 w-full bg-brand-surface border border-brand-border rounded-xl shadow-lg overflow-hidden hidden z-[100] max-h-96 overflow-y-auto">
                    <ul id="search-results" class="py-2 text-sm text-brand-text">
                        <!-- Results injected here -->
                    </ul>
                </div>
            </div>

            <div class="flex items-center justify-end gap-x-2 ms-auto lg:w-auto">
                <div class="flex flex-row items-center justify-end gap-2">
                    <div class="flex items-center space-x-3 bg-gray-100 dark:bg-neutral-800 py-1.5 px-3 rounded-full">
                        <span class="text-xs font-bold text-gray-800 dark:text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <img class="w-7 h-7 rounded-full border border-gray-200 dark:border-neutral-700 shadow-sm bg-white dark:bg-neutral-800" src="<?php echo 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($_SESSION['email'] ?? ''))) . '?s=80&d=monsterid'; ?>" alt="User Avatar">
                    </div>
                </div>

                <!-- Navigation Toggle -->
                <button type="button" id="mobile-menu-toggle" class="lg:hidden w-[2.375rem] h-[2.375rem] inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-neutral-700" aria-label="Toggle navigation">
                    <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                </button>
            </div>
        </nav>
    </header>
    <!-- ========== END HEADER ========== -->

    <!-- ========== MAIN CONTENT ========== -->
    <!-- Sidebar -->
    <div id="application-sidebar" class="transition-all duration-300 transform -translate-x-full fixed top-0 start-0 bottom-0 z-[60] w-64 bg-white border-e border-gray-200 pt-7 pb-10 overflow-y-auto lg:translate-x-0 lg:end-auto lg:bottom-0 [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500 dark:bg-neutral-900 dark:border-neutral-700">
        <div class="px-6 flex justify-between items-center">
            <!-- Logo Area -->
            <a href="index.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-primary to-brand-secondary flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform duration-300">
                    <i class="fa-solid fa-cube text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent group-hover:opacity-90 transition-opacity"><?php echo SITE_NAME; ?></h1>
                    <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider dark:text-neutral-500"><?php echo SITE_SUBTITLE; ?></p>
                </div>
            </a>
            <!-- Mobile Close Button -->
            <button type="button" id="mobile-menu-close" class="lg:hidden w-[2.375rem] h-[2.375rem] inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-neutral-700">
                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <nav class="hs-accordion-group p-6 w-full flex flex-col flex-wrap" data-hs-accordion-always-open>
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 dark:text-neutral-500">Menu</div>
            <ul class="space-y-1.5">
                <?php if ($isAdmin): ?>
                    <li>
                        <a href="admin" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'admin.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-chart-line w-4 text-center"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="mods" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo in_array($current_page, ['mods.php', 'add-mod.php', 'upload.php']) ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-gamepad w-4 text-center"></i>
                            Manage Mods
                        </a>
                    </li>
                    <li>
                        <a href="plans" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'plans.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-tags w-4 text-center"></i>
                            Manage Plans
                        </a>
                    </li>
                    <li>
                        <a href="licenses" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo in_array($current_page, ['licenses.php', 'add-license.php']) ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-key w-4 text-center"></i>
                            License Keys
                        </a>
                    </li>
                    <li>
                        <a href="approvals" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'approvals.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-cart-flatbed w-4 text-center"></i>
                            Orders
                        </a>
                    </li>
                    <li>
                        <a href="users" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'users.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-users w-4 text-center"></i>
                            Manage Clients
                        </a>
                    </li>
                    <li>
                        <a href="settings" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'settings.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-cog w-4 text-center"></i>
                            Site Settings
                        </a>
                    </li>
                    <li>
                        <a href="docs" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'docs.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-book w-4 text-center"></i>
                            API Docs
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="dashboard" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'dashboard.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-house w-4 text-center"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="keys" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'keys.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-store w-4 text-center"></i>
                            Store
                        </a>
                    </li>
                    <li>
                        <a href="orders" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'orders.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-shopping-bag w-4 text-center"></i>
                            My Orders
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="mt-8">
                <ul class="space-y-1.5">
                    <li>
                        <a href="profile" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-neutral-800 <?php echo $current_page == 'profile.php' ? 'bg-gray-100 dark:bg-neutral-800 font-semibold text-brand-primary' : 'text-gray-700 dark:text-neutral-400' ?>">
                            <i class="fa-solid fa-user-circle w-4 text-center"></i>
                            My Profile
                        </a>
                    </li>
                    <li>
                        <a href="logout" class="flex items-center gap-x-3.5 py-2 px-2.5 text-sm rounded-lg text-red-600 hover:bg-red-50 dark:text-red-500 dark:hover:bg-red-800/10">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>
                            Log Out
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
    <!-- End Sidebar -->

    <!-- Content -->
    <div class="w-full pt-10 px-4 sm:px-6 md:px-8 lg:ps-72 pb-10 flex-1">
<?php else: ?>
    <!-- Non-logged in layout -->
    <main class="w-full flex-1">
<?php endif; ?>

