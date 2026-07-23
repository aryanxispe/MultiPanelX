<?php
require_once 'includes/auth.php';

// Check if user is already logged in
$isLoggedIn = isLoggedIn();
$isAdmin = $isLoggedIn && isAdmin();

// Get redirect URL for logged in users
$dashboardUrl = '';
if ($isLoggedIn) {
    $dashboardUrl = $isAdmin ? 'admin.php' : 'dashboard.php';
}

// Fetch active mods for the store showcase
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT * FROM mods WHERE status = 'active' ORDER BY created_at DESC");
$activeMods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME . ' ' . SITE_SUBTITLE; ?> - Premium Mod APK Manager</title>
    <meta name="description" content="Professional Mod APK management system with secure distribution and user management.">
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
                    icon.className = 'fa-solid fa-moon theme-toggle-icon';
                } else {
                    icon.className = 'fa-solid fa-sun theme-toggle-icon';
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', updateThemeIcons);
    </script>
</head>
<body class="bg-brand-bg text-brand-text font-sans antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Navbar -->
    <header class="border-b border-brand-border/60 bg-brand-surface/40 backdrop-blur-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="index" class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-primary to-brand-secondary flex items-center justify-center shadow-glow group-hover:scale-105 transition-transform duration-300">
                    <i class="fa-solid fa-cube text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent"><?php echo SITE_NAME; ?></h1>
                    <p class="text-[10px] text-brand-muted uppercase font-bold tracking-wider"><?php echo SITE_SUBTITLE; ?></p>
                </div>
            </a>
            
            <div class="flex items-center space-x-4">
                <button onclick="toggleTheme()" class="w-9 h-9 rounded-xl border border-brand-border bg-brand-surface/40 hover:bg-brand-surface flex items-center justify-center text-brand-muted hover:text-brand-text transition-all duration-300">
                    <i class="fa-solid fa-sun theme-toggle-icon"></i>
                </button>
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $dashboardUrl; ?>" class="px-4 py-2 bg-brand-primary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all shadow-glow">
                        Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="login" class="text-xs font-bold text-brand-muted hover:text-brand-text transition-colors">
                        Sign In
                    </a>
                    <a href="register" class="px-4 py-2 bg-brand-primary text-white text-xs font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all shadow-glow">
                        Get Started
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="flex-1 flex items-center py-16 md:py-24 animate-fade-in">
        <div class="max-w-7xl mx-auto px-6 w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <!-- Hero Content -->
            <div class="space-y-6">
                <span class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-brand-primary/10 border border-brand-primary/30 text-xs font-bold text-brand-primary">
                    <span class="w-2 h-2 rounded-full bg-brand-primary animate-pulse"></span>
                    <span>Version 1.0 Stable</span>
                </span>
                <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight leading-tight text-glow-primary">
                    Premium Mod APK <br>
                    <span class="bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent">Management System</span>
                </h2>
                <p class="text-sm md:text-base text-brand-muted leading-relaxed max-w-lg">
                    <?php echo SITE_NAME . ' ' . SITE_SUBTITLE; ?> provides a secure, fully-featured dashboard to manage license keys, authorize connected devices, distribute game mods, and control billing with instant top-ups.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 pt-2">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $dashboardUrl; ?>" class="inline-flex items-center justify-center px-6 py-3 bg-brand-primary text-white text-sm font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all space-x-2 shadow-glow">
                            <i class="fa-solid fa-gauge"></i>
                            <span>My Dashboard</span>
                        </a>
                    <?php else: ?>
                        <a href="register" class="inline-flex items-center justify-center px-6 py-3 bg-brand-primary text-white text-sm font-bold rounded-xl hover:opacity-90 active:scale-[0.98] transition-all space-x-2 shadow-glow">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Create Account</span>
                        </a>
                        <a href="login" class="inline-flex items-center justify-center px-6 py-3 bg-brand-surface border border-brand-border text-brand-text text-sm font-bold rounded-xl hover:bg-brand-surface/85 active:scale-[0.98] transition-all space-x-2">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Sign In</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Features Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
                    <div class="w-10 h-10 rounded-xl bg-brand-primary/10 border border-brand-primary/20 flex items-center justify-center text-brand-primary mb-4 shadow-glow">
                        <i class="fa-solid fa-shield-halved text-lg"></i>
                    </div>
                    <h4 class="text-sm font-bold text-brand-text mb-2">Secure DRM & Licensing</h4>
                    <p class="text-xs text-brand-muted leading-relaxed">Encrypted single/bulk keys with flexible hourly, daily, and monthly validation controls.</p>
                </div>

                <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
                    <div class="w-10 h-10 rounded-xl bg-brand-secondary/10 border border-brand-secondary/20 flex items-center justify-center text-brand-secondary mb-4 shadow-glow-cyan">
                        <i class="fa-solid fa-cloud-arrow-down text-lg"></i>
                    </div>
                    <h4 class="text-sm font-bold text-brand-text mb-2">APK Distribution</h4>
                    <p class="text-xs text-brand-muted leading-relaxed">Direct user downloads for authorized mods with versioning, change logs, and direct triggers.</p>
                </div>

                <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
                    <div class="w-10 h-10 rounded-xl bg-brand-success/10 border border-brand-success/20 flex items-center justify-center text-brand-success mb-4">
                        <i class="fa-solid fa-indian-rupee-sign text-lg"></i>
                    </div>
                    <h4 class="text-sm font-bold text-brand-text mb-2">Wallet System</h4>
                    <p class="text-xs text-brand-muted leading-relaxed">Recharge balances instantly to purchase and generate new license keys on demand.</p>
                </div>

                <div class="glass-card rounded-2xl p-6 border border-brand-border/60">
                    <div class="w-10 h-10 rounded-xl bg-brand-warning/10 border border-brand-warning/20 flex items-center justify-center text-brand-warning mb-4">
                        <i class="fa-solid fa-mobile-screen-button text-lg"></i>
                    </div>
                    <h4 class="text-sm font-bold text-brand-text mb-2">Device Management</h4>
                    <p class="text-xs text-brand-muted leading-relaxed">Allow users to reset registered device authorizations securely with password checks.</p>
                </div>
            </div>

            <!-- Store Showcase Section -->
            <?php if (!empty($activeMods)): ?>
            <div class="w-full lg:col-span-2 mt-12 pt-12 border-t border-brand-border/60">
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-extrabold tracking-tight text-brand-text mb-3">Available Mods</h2>
                    <p class="text-sm text-brand-muted max-w-2xl mx-auto">Browse our latest premium mods. Contact an authorized reseller to purchase a license key.</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($activeMods as $mod): ?>
                        <div class="glass-card rounded-2xl overflow-hidden border border-brand-border/60 flex flex-col hover:border-brand-primary/40 transition-colors group">
                            <?php if (!empty($mod['image_url']) && file_exists($mod['image_url'])): ?>
                                <div class="h-48 w-full bg-brand-bg/50 overflow-hidden relative">
                                    <img src="<?php echo htmlspecialchars($mod['image_url']); ?>" alt="<?php echo htmlspecialchars($mod['name']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                            <?php else: ?>
                                <div class="h-48 w-full bg-gradient-to-br from-brand-primary/20 to-brand-secondary/20 flex items-center justify-center">
                                    <i class="fa-solid fa-gamepad text-4xl text-brand-primary/40"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-bold text-brand-text"><?php echo htmlspecialchars($mod['name']); ?></h3>
                                    <?php if (!empty($mod['version'])): ?>
                                        <span class="px-2 py-1 bg-brand-primary/10 text-brand-primary text-[10px] font-bold rounded-md">
                                            <?php echo htmlspecialchars($mod['version']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-xs text-brand-muted mb-4 line-clamp-3 flex-1"><?php echo htmlspecialchars($mod['description']); ?></p>
                                
                                <?php if (!empty($mod['features'])): ?>
                                    <div class="mb-5 space-y-1.5">
                                        <?php 
                                        $features = array_filter(array_map('trim', explode(',', $mod['features'])));
                                        foreach (array_slice($features, 0, 3) as $feature): 
                                        ?>
                                            <div class="flex items-center space-x-2 text-[11px] text-brand-text">
                                                <i class="fa-solid fa-check text-brand-success text-[10px]"></i>
                                                <span><?php echo htmlspecialchars($feature); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($features) > 3): ?>
                                            <div class="text-[10px] text-brand-muted pl-5">+ <?php echo count($features) - 3; ?> more</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <button onclick="alert('Please contact an authorized reseller on Telegram or Discord to purchase a key for <?php echo htmlspecialchars($mod['name']); ?>.')" class="w-full py-2.5 bg-brand-surface border border-brand-border hover:bg-brand-primary hover:text-white hover:border-brand-primary active:scale-[0.98] transition-all rounded-xl text-xs font-bold text-brand-text">
                                    Get License Key
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <!-- End Store Showcase -->

        </div>
    </main>

    <footer class="border-t border-brand-border/60 py-6 bg-brand-surface/20">
        <div class="max-w-7xl mx-auto px-6 text-brand-muted">
            <?php 
            $footer_html = base64_decode("PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBtZDpmbGV4LXJvdyBpdGVtcy1jZW50ZXIganVzdGlmeS1iZXR3ZWVuIGdhcC00IHRleHQteHMgdGV4dC1icmFuZC1tdXRlZCB3LWZ1bGwiPjxwIGNsYXNzPSJ0ZXh0LWNlbnRlciBtZDp0ZXh0LWxlZnQiPiZjb3B5OyBfX1lFQVJfXyBfX1NJVEVfXy4gQWxsIHJpZ2h0cyByZXNlcnZlZC48L3A+PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBzbTpmbGV4LXJvdyBpdGVtcy1jZW50ZXIgZ2FwLTMgc206Z2FwLTQgdGV4dC1jZW50ZXIgc206dGV4dC1yaWdodCI+PGEgaHJlZj0iaHR0cHM6Ly90Lm1lL0FSWUFOSVNQRSIgdGFyZ2V0PSJfYmxhbmsiIGNsYXNzPSJob3Zlcjp0ZXh0LWJyYW5kLXByaW1hcnkgdHJhbnNpdGlvbi1jb2xvcnMgZmxleCBpdGVtcy1jZW50ZXIgc3BhY2UteC0xLjUgZm9udC1tZWRpdW0iPjxpIGNsYXNzPSJmYS1icmFuZHMgZmEtdGVsZWdyYW0gdGV4dC14cyB0ZXh0LWJyYW5kLXByaW1hcnkiPjwvaT48c3Bhbj5EZXNpZ25lZCBhbmQgZGV2ZWxvcGVkIGJ5IEBhcnlhbmlzcGU8L3NwYW4+PC9hPjxzcGFuIGNsYXNzPSJoaWRkZW4gc206aW5saW5lIHRleHQtYnJhbmQtYm9yZGVyLzQwIj58PC9zcGFuPjxhIGhyZWY9Imh0dHBzOi8vYXJ5YW5pc3BlaG9zdC5pbi8iIHRhcmdldD0iX2JsYW5rIiBjbGFzcz0iaG92ZXI6dGV4dC1icmFuZC1wcmltYXJ5IHRyYW5zaXRpb24tY29sb3JzIGZsZXggaXRlbXMtY2VudGVyIHNwYWNlLXgtMS41IGZvbnQtbWVkaXVtIj48aSBjbGFzcz0iZmEtc29saWQgZmEtc2VydmVyIHRleHQteHMgdGV4dC1icmFuZC1zZWNvbmRhcnkiPjwvaT48c3Bhbj5Qb3dlcmVkIGJ5IEFyeWFuaXNwZSBIb3N0PC9zcGFuPjwvYT4gPC9kaXY+PC9kaXY+");
            echo str_replace(
                ['__YEAR__', '__SITE__'],
                [date('Y'), SITE_NAME . ' ' . SITE_SUBTITLE],
                $footer_html
            );
            ?>
        </div>
    </footer>

</body>
</html>