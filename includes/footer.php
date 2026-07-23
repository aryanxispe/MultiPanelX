<?php if ($isLoggedIn): ?>
    </div> <!-- End Content -->
<?php else: ?>
    </main>
<?php endif; ?>

    <!-- Global Footer -->
    <footer class="mt-auto border-t border-gray-200 py-6 bg-white dark:bg-neutral-900 dark:border-neutral-700 <?php echo $isLoggedIn ? 'lg:ps-72' : ''; ?>">
        <div class="max-w-7xl mx-auto px-6 text-gray-500 dark:text-neutral-500">
            <?php 
            $footer_html = base64_decode("PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBtZDpmbGV4LXJvdyBpdGVtcy1jZW50ZXIganVzdGlmeS1iZXR3ZWVuIGdhcC00IHRleHQteHMgdGV4dC1icmFuZC1tdXRlZCB3LWZ1bGwiPjxwIGNsYXNzPSJ0ZXh0LWNlbnRlciBtZDp0ZXh0LWxlZnQiPiZjb3B5OyBfX1lFQVJfXyBfX1NJVEVfXy4gQWxsIHJpZ2h0cyByZXNlcnZlZC48L3A+PGRpdiBjbGFzcz0iZmxleCBmbGV4LWNvbCBzbTpmbGV4LXJvdyBpdGVtcy1jZW50ZXIgZ2FwLTMgc206Z2FwLTQgdGV4dC1jZW50ZXIgc206dGV4dC1yaWdodCI+PGEgaHJlZj0iaHR0cHM6Ly90Lm1lL0FSWUFOSVNQRSIgdGFyZ2V0PSJfYmxhbmsiIGNsYXNzPSJob3Zlcjp0ZXh0LWJyYW5kLXByaW1hcnkgdHJhbnNpdGlvbi1jb2xvcnMgZmxleCBpdGVtcy1jZW50ZXIgc3BhY2UteC0xLjUgZm9udC1tZWRpdW0iPjxpIGNsYXNzPSJmYS1icmFuZHMgZmEtdGVsZWdyYW0gdGV4dC14cyB0ZXh0LWJyYW5kLXByaW1hcnkiPjwvaT48c3Bhbj5EZXNpZ25lZCBhbmQgZGV2ZWxvcGVkIGJ5IEBhcnlhbmlzcGU8L3NwYW4+PC9hPjxzcGFuIGNsYXNzPSJoaWRkZW4gc206aW5saW5lIHRleHQtYnJhbmQtYm9yZGVyLzQwIj58PC9zcGFuPjxhIGhyZWY9Imh0dHBzOi8vYXJ5YW5pc3BlaG9zdC5pbi8iIHRhcmdldD0iX2JsYW5rIiBjbGFzcz0iaG92ZXI6dGV4dC1icmFuZC1wcmltYXJ5IHRyYW5zaXRpb24tY29sb3JzIGZsZXggaXRlbXMtY2VudGVyIHNwYWNlLXgtMS41IGZvbnQtbWVkaXVtIj48aSBjbGFzcz0iZmEtc29saWQgZmEtc2VydmVyIHRleHQteHMgdGV4dC1icmFuZC1zZWNvbmRhcnkiPjwvaT48c3Bhbj5Qb3dlcmVkIGJ5IEFyeWFuaXNwZSBIb3N0PC9zcGFuPjwvYT48L2Rpdj48L2Rpdj4=");
            echo str_replace(
                ['__YEAR__', '__SITE__'],
                [date('Y'), SITE_NAME . ' ' . SITE_SUBTITLE],
                $footer_html
            );
            ?>
        </div>
    </footer>

<!-- Script for Mobile Navigation Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const closeBtn = document.getElementById('mobile-menu-close');
    const sidebar = document.getElementById('application-sidebar');
    
    function toggleSidebar(e) {
        if (e) e.stopPropagation();
        if (sidebar) {
            sidebar.classList.toggle('-translate-x-full');
        }
    }
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (sidebar && !sidebar.classList.contains('-translate-x-full')) {
            if (!sidebar.contains(e.target) && (!toggleBtn || !toggleBtn.contains(e.target))) {
                sidebar.classList.add('-translate-x-full');
            }
        }
    });
});

// Helper for Copy to Clipboard UI animations
function copyToClipboard(text, buttonElement) {
    navigator.clipboard.writeText(text).then(function() {
        const originalHtml = buttonElement.innerHTML;
        buttonElement.innerHTML = '<i class="fa-solid fa-check text-brand-success mr-1"></i> Copied!';
        buttonElement.classList.add('bg-brand-success/10', 'text-brand-success', 'border-brand-success/20');
        
        setTimeout(function() {
            buttonElement.innerHTML = originalHtml;
            buttonElement.classList.remove('bg-brand-success/10', 'text-brand-success', 'border-brand-success/20');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}

// Global Search Logic
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('global-search-input');
    const searchDropdown = document.getElementById('search-dropdown');
    const searchResults = document.getElementById('search-results');
    let debounceTimer;

    // Handle Ctrl+K shortcut
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (searchInput) {
                searchInput.focus();
            }
        }
    });

    if (searchInput && searchDropdown && searchResults) {
        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchDropdown.contains(e.target)) {
                searchDropdown.classList.add('hidden');
            }
        });

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();
            
            if (query.length === 0) {
                searchDropdown.classList.add('hidden');
                return;
            }

            debounceTimer = setTimeout(() => {
                fetch('/ajax_search?q=' + encodeURIComponent(query), {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                    .then(response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.indexOf('application/json') === -1) {
                            throw new Error('Not a JSON response. You might be logged out or redirected.');
                        }
                        return response.json();
                    })
                    .then(data => {
                        searchResults.innerHTML = '';
                        if (data.error === 'not_logged_in') {
                            searchResults.innerHTML = '<li class="px-4 py-4 text-center text-red-500 text-xs font-bold"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Session expired or cookies blocked. Try logging out and back in.</li>';
                        } else if (data.results && data.results.length > 0) {
                            data.results.forEach(item => {
                                const li = document.createElement('li');
                                li.innerHTML = `
                                    <a href="${item.url}.php" class="flex items-center px-4 py-2.5 hover:bg-brand-bg/50 transition-colors group">
                                        <div class="w-8 h-8 rounded-lg bg-brand-bg flex items-center justify-center mr-3 group-hover:bg-brand-primary/10 group-hover:text-brand-primary text-brand-muted transition-colors">
                                            <i class="${item.icon}"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold truncate text-brand-text group-hover:text-brand-primary transition-colors">${item.title}</div>
                                            <div class="text-[10px] uppercase font-bold tracking-wider text-brand-muted">${item.type}</div>
                                        </div>
                                    </a>
                                `;
                                searchResults.appendChild(li);
                            });
                        } else {
                            searchResults.innerHTML = '<li class="px-4 py-4 text-center text-brand-muted text-xs">No results found</li>';
                        }
                        searchDropdown.classList.remove('hidden');
                    })
                    .catch(err => {
                        console.error('Search error:', err);
                        searchResults.innerHTML = `<li class="px-4 py-4 text-center text-red-500 text-xs font-bold">Error: ${err.message}</li>`;
                        searchDropdown.classList.remove('hidden');
                    });
            }, 300); // 300ms debounce
        });
    }
});
</script>
<?php if (defined('SHOW_TELEGRAM_ICON') && SHOW_TELEGRAM_ICON === '1' && defined('TELEGRAM_LINK') && !empty(TELEGRAM_LINK)): ?>
<!-- Floating Telegram Icon -->
<a href="<?php echo htmlspecialchars(TELEGRAM_LINK); ?>" target="_blank" class="fixed bottom-6 right-6 z-[90] w-14 h-14 bg-brand-primary text-white rounded-full flex items-center justify-center shadow-lg hover:scale-110 hover:shadow-brand-primary/50 transition-all duration-300 animate-bounce" aria-label="Join our Telegram">
    <i class="fa-brands fa-telegram text-3xl ml-[-2px]"></i>
</a>
<?php endif; ?>

    <!-- Preline UI Script -->
    <script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
</body>
</html>
