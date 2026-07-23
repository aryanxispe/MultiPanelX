<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    verify_csrf();
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $pdo = null;
        try {
            $pdo = getDBConnection();
            
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username or email already exists';
            } else {
                $pdo->beginTransaction();
                
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                // Insert user without a referrer (NULL)
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, referred_by) VALUES (?, ?, ?, NULL)");
                $stmt->execute([$username, $email, $hashedPassword]);
                $userId = $pdo->lastInsertId();
                
                $pdo->commit();
                
                // Auto-login the new user
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = 'user';
                $_SESSION['balance'] = 0.00;
                
                header('Location: dashboard.php');
                exit();
            }
        } catch (Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME . ' ' . SITE_SUBTITLE; ?></title>

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
    </style>
</head>
<body class="bg-brand-bg text-brand-text font-sans antialiased min-h-screen flex flex-col items-center justify-between p-4">

    <div class="w-full max-w-lg my-auto animate-fade-in">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-primary to-brand-secondary items-center justify-center shadow-sm mb-4">
                <i class="fa-solid fa-cube text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-brand-primary to-brand-secondary bg-clip-text text-transparent"><?php echo SITE_NAME; ?></h1>
            <p class="text-xs uppercase font-bold tracking-widest text-brand-muted mt-1"><?php echo SITE_SUBTITLE; ?> Key Manager</p>
        </div>

        <!-- Register Card Preline -->
        <div class="bg-brand-surface border border-brand-border rounded-xl">
            <div class="p-4 sm:p-7">
                <div class="text-center">
                    <h2 class="block text-2xl font-bold text-gray-800 dark:text-white">Create Account</h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-neutral-400">
                        Join Aryanispe Multipanel today
                    </p>
                </div>

                <div class="mt-5">
                    <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 text-sm text-red-800 rounded-lg p-4 mb-5 dark:bg-red-800/10 dark:border-red-900 dark:text-red-500 flex items-center space-x-2" role="alert">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="bg-teal-50 border border-teal-200 text-sm text-teal-800 rounded-lg p-4 mb-5 dark:bg-teal-800/10 dark:border-teal-900 dark:text-teal-500 flex items-center space-x-2" role="alert">
                            <i class="fa-solid fa-circle-check"></i>
                            <span><?php echo htmlspecialchars($success); ?></span>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" id="registerForm">
    <?php echo csrf_field(); ?>
                        <div class="grid gap-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Form Group -->
                                <div>
                                    <label for="username" class="block text-sm mb-2 dark:text-white">Username *</label>
                                    <div class="relative">
                                        <input type="text" id="username" name="username" required
                                               value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                               class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-primary focus:ring-brand-primary disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-brand-primary"
                                               placeholder="e.g. aryan123">
                                    </div>
                                </div>
                                <!-- End Form Group -->

                                <!-- Form Group -->
                                <div>
                                    <label for="email" class="block text-sm mb-2 dark:text-white">Email Address *</label>
                                    <div class="relative">
                                        <input type="email" id="email" name="email" required
                                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                               class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-primary focus:ring-brand-primary disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-brand-primary"
                                               placeholder="e.g. name@example.com">
                                    </div>
                                </div>
                                <!-- End Form Group -->
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- Form Group -->
                                <div>
                                    <div class="flex justify-between items-center">
                                        <label for="password" class="block text-sm mb-2 dark:text-white">Password *</label>
                                    </div>
                                    <div class="relative">
                                        <input type="password" id="password" name="password" required
                                               class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-primary focus:ring-brand-primary disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-brand-primary"
                                               placeholder="Min 6 characters">
                                    </div>
                                </div>
                                <!-- End Form Group -->

                                <!-- Form Group -->
                                <div>
                                    <div class="flex justify-between items-center">
                                        <label for="confirm_password" class="block text-sm mb-2 dark:text-white">Confirm Password *</label>
                                    </div>
                                    <div class="relative">
                                        <input type="password" id="confirm_password" name="confirm_password" required
                                               class="py-3 px-4 block w-full border-gray-200 rounded-lg text-sm focus:border-brand-primary focus:ring-brand-primary disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-brand-primary"
                                               placeholder="Re-type password">
                                    </div>
                                </div>
                                <!-- End Form Group -->
                            </div>

                            <button type="submit" id="submitBtn" class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-brand-primary text-white hover:bg-brand-primary/90 disabled:opacity-50 disabled:pointer-events-none mt-2">
                                <i class="fa-solid fa-user-plus" id="btnIcon"></i>
                                <span id="btnText">Register Account</span>
                            </button>
                        </div>
                    </form>
                    <!-- End Form -->

                    <div class="mt-6 text-center">
                        <p class="text-sm text-gray-600 dark:text-neutral-400">
                            Already have an account?
                            <a class="text-brand-primary decoration-2 hover:underline font-medium" href="login">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Footer -->
    <footer class="w-full max-w-7xl mx-auto px-6 py-6 text-brand-muted border-t border-brand-border/20">
        <?php 
        $footer_html = base64_decode("PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBtZDpmbGV4LXJvdyBpdGVtcy1jZW50ZXIganVzdGlmeS1iZXR3ZWVuIGdhcC00IHRleHQteHMgdGV4dC1icmFuZC1tdXRlZCB3LWZ1bGwiPjxwIGNsYXNzPSJ0ZXh0LWNlbnRlciBtZDp0ZXh0LWxlZnQiPiZjb3B5OyBfX1lFQVJfXyBfX1NJVEVfXy4gQWxsIHJpZ2h0cyByZXNlcnZlZC48L3A+PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBzbTpmbGV4LXJvdyBpdGVtcy1jZW50ZXIgZ2FwLTMgc206Z2FwLTQgdGV4dC1jZW50ZXIgc206dGV4dC1yaWdodCI+PGEgaHJlZj0iaHR0cHM6Ly90Lm1lL0FSWUFOSVNQRSIgdGFyZ2V0PSJfYmxhbmsiIGNsYXNzPSJob3Zlcjp0ZXh0LWJyYW5kLXByaW1hcnkgdHJhbnNpdGlvbi1jb2xvcnMgZmxleCBpdGVtcy1jZW50ZXIgc3BhY2UteC0xLjUgZm9udC1tZWRpdW0iPjxpIGNsYXNzPSJmYS1icmFuZHMgZmEtdGVsZWdyYW0gdGV4dC14cyB0ZXh0LWJyYW5kLXByaW1hcnkiPjwvaT48c3Bhbj5EZXNpZ25lZCBhbmQgZGV2ZWxvcGVkIGJ5IEBhcnlhbmlzcGU8L3NwYW4+PC9hPjxzcGFuIGNsYXNzPSJoaWRkZW4gc206aW5saW5lIHRleHQtYnJhbmQtYm9yZGVyLzQwIj58PC9zcGFuPjxhIGhyZWY9Imh0dHBzOi8vYXJ5YW5pc3BlaG9zdC5pbi8iIHRhcmdldD0iX2JsYW5rIiBjbGFzcz0iaG92ZXI6dGV4dC1icmFuZC1wcmltYXJ5IHRyYW5zaXRpb24tY29sb3JzIGZsZXggaXRlbXMtY2VudGVyIHNwYWNlLXgtMS41IGZvbnQtbWVkaXVtIj48aSBjbGFzcz0iZmEtc29saWQgZmEtc2VydmVyIHRleHQteHMgdGV4dC1icmFuZC1zZWNvbmRhcnkiPjwvaT48c3Bhbj5Qb3dlcmVkIGJ5IEFyeWFuaXNwZSBIb3N0PC9zcGFuPjwvYT48L2Rpdj48L2Rpdj4=");
        echo str_replace(
            ['__YEAR__', '__SITE__'],
            [date('Y'), SITE_NAME . ' ' . SITE_SUBTITLE],
            $footer_html
        );
        ?>
    </footer>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const btn = document.getElementById('submitBtn');
            const icon = document.getElementById('btnIcon');
            const text = document.getElementById('btnText');
            
            // Apply loading state
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            icon.className = 'fa-solid fa-spinner animate-spin';
            text.innerText = 'Creating Account...';
            
            // 700ms premium processing delay
            setTimeout(function() {
                form.submit();
            }, 700);
        });
    </script>
    <!-- Preline UI Script -->
    <script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
</body>
</html>

