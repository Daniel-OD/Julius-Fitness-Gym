<script>
    (function () {
        const stored = localStorage.getItem('theme');
        const isDark = stored !== 'light';
        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
