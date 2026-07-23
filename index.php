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

    <!-- Hero Section Preline -->
    <main class="flex-1">
        <div class="relative overflow-hidden before:absolute before:top-0 before:start-1/2 before:bg-[url('https://preline.co/assets/svg/examples/polygon-bg-element.svg')] before:no-repeat before:bg-top before:bg-cover before:size-full before:-z-[1] before:transform before:-translate-x-1/2 dark:before:bg-[url('https://preline.co/assets/svg/examples-dark/polygon-bg-element.svg')]">
            <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-10 animate-fade-in">
                <!-- Announcement Banner -->
                <div class="flex justify-center">
                    <a class="inline-flex items-center gap-x-2 bg-white border border-gray-200 text-sm text-gray-800 p-1 ps-3 rounded-full transition hover:border-gray-300 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200 dark:hover:border-neutral-600" href="#">
                        Ultimate Mod Management
                        <span class="py-1.5 px-2.5 inline-flex justify-center items-center gap-x-2 rounded-full bg-gray-200 text-gray-600 font-semibold text-sm dark:bg-neutral-700 dark:text-neutral-400">
                            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </span>
                    </a>
                </div>
                <!-- End Announcement Banner -->

                <!-- Title -->
                <div class="mt-5 max-w-2xl text-center mx-auto">
                    <h1 class="block font-bold text-gray-800 text-4xl md:text-5xl lg:text-6xl dark:text-neutral-200">
                        Premium Mod APK
                        <span class="bg-clip-text bg-gradient-to-tl from-brand-primary to-brand-secondary text-transparent">Management System</span>
                    </h1>
                </div>
                <!-- End Title -->

                <div class="mt-5 max-w-3xl text-center mx-auto">
                    <p class="text-lg text-gray-600 dark:text-neutral-400">
                        <?php echo SITE_NAME . ' ' . SITE_SUBTITLE; ?> provides a secure, fully-featured dashboard to manage license keys, authorize connected devices, distribute game mods, and control billing with instant top-ups.
                    </p>
                </div>

                <!-- Buttons -->
                <div class="mt-8 gap-3 flex justify-center">
                    <?php if ($isLoggedIn): ?>
                        <a class="inline-flex justify-center items-center gap-x-3 text-center bg-gradient-to-tl from-brand-primary to-brand-secondary hover:from-brand-secondary hover:to-brand-primary border border-transparent text-white text-sm font-medium rounded-md focus:outline-none focus:ring-1 focus:ring-gray-600 py-3 px-4 dark:focus:ring-offset-gray-800" href="<?php echo $dashboardUrl; ?>">
                            My Dashboard
                            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                    <?php else: ?>
                        <a class="inline-flex justify-center items-center gap-x-3 text-center bg-gradient-to-tl from-brand-primary to-brand-secondary hover:from-brand-secondary hover:to-brand-primary border border-transparent text-white text-sm font-medium rounded-md focus:outline-none focus:ring-1 focus:ring-gray-600 py-3 px-4 dark:focus:ring-offset-gray-800" href="register">
                            Create Account
                            <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                        </a>
                        <a class="inline-flex justify-center items-center gap-x-3 text-center bg-white border border-gray-200 text-gray-800 text-sm font-medium rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800 py-3 px-4" href="login">
                            Sign In
                        </a>
                    <?php endif; ?>
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
                        <div class="glass-card rounded-3xl overflow-hidden border border-brand-border/60 flex flex-col hover:border-brand-primary/50 hover:shadow-[0_0_30px_-10px_rgba(var(--brand-primary-rgb),0.3)] transition-all duration-300 group">
                            <?php if (!empty($mod['image_url']) && file_exists($mod['image_url'])): ?>
                                <div class="aspect-[4/3] w-full bg-brand-bg/50 overflow-hidden relative">
                                    <img src="<?php echo htmlspecialchars($mod['image_url']); ?>" alt="<?php echo htmlspecialchars($mod['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
                                    <div class="absolute inset-0 bg-gradient-to-t from-brand-surface to-transparent opacity-80"></div>
                                </div>
                            <?php else: ?>
                                <div class="aspect-[4/3] w-full bg-gradient-to-br from-brand-primary/20 to-brand-secondary/20 flex items-center justify-center relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-t from-brand-surface to-transparent opacity-80"></div>
                                    <i class="fa-solid fa-gamepad text-6xl text-brand-primary/40 group-hover:scale-110 transition-transform duration-700"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-8 flex-1 flex flex-col relative z-10 -mt-12">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-xl font-extrabold text-brand-text drop-shadow-md"><?php echo htmlspecialchars($mod['name']); ?></h3>
                                    <?php if (!empty($mod['version'])): ?>
                                        <span class="px-2.5 py-1 bg-brand-primary/20 border border-brand-primary/30 text-brand-primary text-[10px] font-black rounded-lg backdrop-blur-sm shadow-glow">
                                            <?php echo htmlspecialchars($mod['version']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-sm text-brand-muted mb-6 line-clamp-3 flex-1 leading-relaxed"><?php echo htmlspecialchars($mod['description']); ?></p>
                                
                                <?php if (!empty($mod['features'])): ?>
                                    <div class="mb-8 space-y-2.5 bg-brand-bg/50 rounded-2xl p-4 border border-brand-border/40">
                                        <?php 
                                        $features = array_filter(array_map('trim', preg_split('/[\n,]+/', $mod['features'])));
                                        foreach (array_slice($features, 0, 4) as $feature): 
                                        ?>
                                            <div class="flex items-center space-x-3 text-xs font-medium text-brand-text/90">
                                                <div class="w-4 h-4 rounded-full bg-brand-success/20 flex items-center justify-center flex-shrink-0">
                                                    <i class="fa-solid fa-check text-brand-success text-[8px]"></i>
                                                </div>
                                                <span><?php echo htmlspecialchars($feature); ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (count($features) > 4): ?>
                                            <div class="text-[10px] text-brand-muted pl-7 font-semibold">+ <?php echo count($features) - 4; ?> more amazing features</div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php 
                                $purchase_link = !empty($mod['purchase_link']) ? htmlspecialchars($mod['purchase_link']) : 'keys';
                                $target = !empty($mod['purchase_link']) ? 'target="_blank"' : '';
                                ?>
                                <a href="<?php echo $purchase_link; ?>" <?php echo $target; ?> class="w-full py-3.5 bg-brand-surface border border-brand-border hover:bg-brand-primary hover:text-white hover:border-brand-primary active:scale-[0.98] transition-all rounded-xl text-sm font-bold text-brand-text shadow-lg group-hover:shadow-brand-primary/20 text-center flex items-center justify-center">
                                    <i class="fa-solid fa-cart-shopping mr-2"></i> Get License Key
                                </a>
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

    <!-- Preline UI Script -->
    <script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
</body>
</html>
