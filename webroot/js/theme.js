(function () {
    var root = document.documentElement;
    var toggle = document.getElementById('theme-toggle');
    if (!toggle) {
        return;
    }

    function sync() {
        var isDark = root.getAttribute('data-theme') === 'dark';
        toggle.setAttribute('aria-pressed', String(isDark));
        var label = isDark ? 'Switch to light theme' : 'Switch to dark theme';
        toggle.setAttribute('aria-label', label);
        toggle.setAttribute('title', label);
    }

    toggle.addEventListener('click', function () {
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        root.setAttribute('data-theme', next);
        try {
            localStorage.setItem('theme', next);
        } catch (e) {
            // Storage unavailable (private mode, etc.) — theme just won't persist.
        }
        sync();
    });

    sync();
})();
