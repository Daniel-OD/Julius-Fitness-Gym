<script>
    (function () {
        const stored = localStorage.getItem('theme');
        const isDark = stored !== 'light';
        document.documentElement.classList.toggle('dark', isDark);
        // #region agent log
        fetch('http://127.0.0.1:7897/ingest/d6f991e9-d6f3-4d92-be2e-7338c0dd4be4',{method:'POST',headers:{'Content-Type':'application/json','X-Debug-Session-Id':'45e8fc'},body:JSON.stringify({sessionId:'45e8fc',location:'theme-script.blade.php:init',message:'theme init',data:{storedTheme:stored,isDark,htmlHasDark:document.documentElement.classList.contains('dark')},timestamp:Date.now(),hypothesisId:'H-A'})}).catch(()=>{});
        // #endregion
    })();
</script>
