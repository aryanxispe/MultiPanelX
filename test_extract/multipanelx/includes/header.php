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
        // Fallback to cached session balance on connection error
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : SITE_NAME . ' ' . SITE_SUBTITLE; ?></title>
    <!-- Theme state initializer (FOUC prevention) -->
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
        }
    </script>
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
        --brand-bg-rgb: 11 15 25;
        --brand-surface-rgb: 17 24 39;
        --brand-border-rgb: 31 41 55;
        --brand-text-rgb: 249 250 251;
        --brand-muted-rgb: 156 163 175;
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
        background-color: rgba(var(--brand-surface-rgb), 0.6);
        border: 1px solid rgba(var(--brand-border-rgb), 0.5);
        box-shadow: 0 0 15px rgba(99, 102, 241, 0.15);
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
    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.remove('light');
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateThemeIcons();
        }

        function updateThemeIcons() {
            const isLight = document.documentElement.classList.contains('light');
            document.querySelectorAll('.theme-toggle-icon').forEach(icon => {
                if (isLight) {
                    icon.className = 'fa-solid fa-moon theme-toggle-icon w-5 text-center';
                } else {
                    icon.className = 'fa-solid fa-sun theme-toggle-icon w-5 text-center';
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', updateThemeIcons);
    </script>
</head>
<body class="bg-brand-bg text-brand-text font-sans antialiased min-h-screen flex flex-col md:flex-row">
<?php if ($isLoggedIn): ?>
    <!-- Sidebar -->
    <aside class="w-full md:w-64 bg-brand-surface/90 border-b md:border-b-0 md:border-r border-brand-border backdrop-blur-md flex flex-col z-20">
        <!-- Logo Area -->
        <div class="p-6 border-b border-brand-border/60 flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-primary to-brand-secondary flex items-center justify-center shadow-glow group-hover:scale-105 transition-transform duration-300">
                    <i class="fa-solid fa-cube text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent group-hover:opacity-90 transition-opacity"><?php echo SITE_NAME; ?></h1>
                    <p class="text-[10px] text-brand-muted uppercase font-bold tracking-wider"><?php echo SITE_SUBTITLE; ?></p>
                </div>
            </a>
            <div class="flex items-center space-x-3.5 md:hidden">
                <div class="flex items-center space-x-2">
                    <?php if (!$isAdmin): ?>
                        <span class="text-xs font-bold text-brand-primary">₹<?php echo number_format((float)$_SESSION['balance'], 2); ?></span>
                    <?php endif; ?>
                    <span class="text-xs font-semibold text-brand-text/90"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="w-7 h-7 rounded-full bg-brand-primary/20 border border-brand-primary/30 flex items-center justify-center text-[10px] font-bold text-brand-primary uppercase">
                        <?php echo substr($_SESSION['username'], 0, 1); ?>
                    </div>
                </div>
                <button id="mobile-menu-toggle" class="text-brand-muted hover:text-brand-text">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav id="sidebar-nav" class="hidden md:flex flex-col flex-1 p-4 space-y-1.5 overflow-y-auto">
            <div class="px-3 mb-2 text-xs font-bold text-brand-muted uppercase tracking-wider">Menu</div>
            
            <?php if ($isAdmin): ?>
                <!-- Admin Links -->
                <a href="admin" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'admin.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>
                <a href="mods" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo in_array($current_page, ['mods.php', 'add-mod.php', 'upload.php']) ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-gamepad w-5 text-center"></i>
                    <span>Manage Mods</span>
                </a>
                <a href="licenses" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo in_array($current_page, ['licenses.php', 'add-license.php']) ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-key w-5 text-center"></i>
                    <span>License Keys</span>
                </a>
                <a href="users" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo in_array($current_page, ['users.php', 'add-balance.php']) ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span>Manage Users</span>
                </a>
                <a href="transactions" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'transactions.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-money-bill-transfer w-5 text-center"></i>
                    <span>Transactions</span>
                </a>
                <a href="referrals" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'referrals.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-ticket w-5 text-center"></i>
                    <span>Referrals</span>
                </a>
                <a href="settings" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'settings.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-sliders w-5 text-center"></i>
                    <span>Settings</span>
                </a>
            <?php else: ?>
                <!-- User Links -->
                <a href="dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'dashboard.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-house w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>
                <a href="keys" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo in_array($current_page, ['keys.php', 'generate.php']) ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-key w-5 text-center"></i>
                    <span>Purchase Keys</span>
                </a>
                <a href="downloads" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'downloads.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-circle-down w-5 text-center"></i>
                    <span>My Downloads</span>
                </a>
                <a href="wallet" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'wallet.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-wallet w-5 text-center"></i>
                    <span>Wallet & Balance</span>
                </a>
                <a href="history" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'history.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-clock-rotate-left w-5 text-center"></i>
                    <span>Transactions</span>
                </a>
                <a href="profile" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'profile.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                    <i class="fa-solid fa-user-gear w-5 text-center"></i>
                    <span>Settings</span>
                </a>
            <?php endif; ?>
            <a href="docs" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-300 <?php echo $current_page == 'docs.php' ? 'bg-brand-primary/10 text-brand-primary border-l-4 border-brand-primary pl-2 shadow-glow' : 'text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text' ?>">
                <i class="fa-solid fa-book w-5 text-center"></i>
                <span>API Docs</span>
            </a>
            
            <button onclick="toggleTheme()" class="w-full flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-brand-muted hover:bg-brand-surface/50 hover:text-brand-text transition-all duration-300">
                <i class="fa-solid fa-sun theme-toggle-icon w-5 text-center"></i>
                <span>Switch Theme</span>
            </button>

            <div class="pt-6 mt-6 border-t border-brand-border/60">
                <a href="logout" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium text-brand-error hover:bg-brand-error/10 transition-colors">
                    <i class="fa-solid fa-right-from-bracket w-5 text-center"></i>
                    <span>Log Out</span>
                </a>
            </div>
        </nav>

        <!-- User Brief info at sidebar bottom -->
        <div class="hidden md:flex p-4 border-t border-brand-border/60 items-center justify-between bg-brand-surface/40">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-full bg-brand-border flex items-center justify-center text-sm font-semibold uppercase text-brand-primary">
                    <?php echo substr($_SESSION['username'], 0, 2); ?>
                </div>
                <div class="truncate w-24">
                    <p class="text-xs font-semibold truncate text-brand-text"><?php echo $_SESSION['username']; ?></p>
                    <p class="text-[10px] text-brand-muted capitalize"><?php echo $_SESSION['role']; ?></p>
                </div>
            </div>
            <?php if (!$isAdmin): ?>
                <div class="bg-brand-primary/10 px-2 py-1 rounded-lg text-xs font-semibold text-brand-primary">
                    ₹<?php echo number_format((float)$_SESSION['balance'], 2); ?>
                </div>
            <?php endif; ?>
        </div>
    </aside>
<?php endif; ?>

<!-- Main Content Area -->
<main class="flex-1 min-w-0 flex flex-col min-h-screen">
    <?php if ($isLoggedIn): ?>
        <header class="hidden md:flex h-16 bg-brand-surface/60 border-b border-brand-border backdrop-blur-md px-6 items-center justify-end z-10">
            
            <div class="flex items-center space-x-4">
                <?php if (!$isAdmin): ?>
                    <div class="flex items-center space-x-2 bg-brand-surface border border-brand-border px-3 py-1.5 rounded-xl shadow-sm">
                        <i class="fa-solid fa-wallet text-brand-primary text-sm"></i>
                        <span class="text-xs text-brand-muted">Balance:</span>
                        <span class="text-sm font-bold text-brand-primary text-glow-primary">₹<?php echo number_format((float)$_SESSION['balance'], 2); ?></span>
                    </div>
                <?php endif; ?>
                
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-brand-muted hidden sm:inline">Logged in as</span>
                    <span class="text-xs font-bold text-brand-text"><?php echo $_SESSION['username']; ?></span>
                    <div class="w-8 h-8 rounded-full bg-brand-primary/20 border border-brand-primary/30 flex items-center justify-center text-xs font-bold text-brand-primary">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                </div>
            </div>
        </header>
    <?php endif; ?>

    <!-- Main Content Body -->
    <div class="p-6 md:p-8 flex-1 overflow-x-hidden animate-fade-in">
