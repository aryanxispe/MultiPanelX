    </div> <!-- End of p-6 / p-8 -->
    <!-- Global Footer -->
    <footer class="mt-auto border-t border-brand-border/40 py-6 bg-brand-surface/20">
        <div class="max-w-7xl mx-auto px-6 text-brand-muted">
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
</main> <!-- End of flex-1 / main -->

<!-- Script for Mobile Navigation Toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('mobile-menu-toggle');
    const nav = document.getElementById('sidebar-nav');
    
    if (toggleBtn && nav) {
        toggleBtn.addEventListener('click', function() {
            nav.classList.toggle('hidden');
            nav.classList.toggle('flex');
        });
    }
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
</script>
</body>
</html>
