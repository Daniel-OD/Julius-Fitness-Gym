<script>
    (function () {
        const stored = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = stored === 'dark' || (stored !== 'light' && prefersDark);
        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
