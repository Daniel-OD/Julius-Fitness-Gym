// Sidebar toggle (mobile), theme switching, and lightweight dropdowns.
// Kept dependency-free on purpose so the UI foundation needs no JS framework.

function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    if (!sidebar) return;

    const open = () => {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        backdrop?.classList.remove('hidden');
    };
    const close = () => {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        backdrop?.classList.add('hidden');
    };

    document.querySelectorAll('[data-sidebar-toggle]').forEach((el) => {
        el.addEventListener('click', open);
    });
    backdrop?.addEventListener('click', close);
    document.querySelectorAll('[data-sidebar-close]').forEach((el) => {
        el.addEventListener('click', close);
    });
}

function applyTheme(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    localStorage.setItem('theme', theme);
}

function initThemeToggle() {
    document.querySelectorAll('[data-theme-toggle]').forEach((el) => {
        el.addEventListener('click', () => {
            const isDark = document.documentElement.classList.contains('dark');
            applyTheme(isDark ? 'light' : 'dark');
        });
    });
}

function initDropdowns() {
    const dropdowns = Array.from(document.querySelectorAll('[data-dropdown]'));

    const closeAll = (except) => {
        dropdowns.forEach((dropdown) => {
            if (dropdown === except) return;
            dropdown.querySelector('[data-dropdown-menu]')?.classList.add('hidden');
        });
    };

    dropdowns.forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');
        if (!trigger || !menu) return;

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = menu.classList.contains('hidden');
            closeAll(dropdown);
            menu.classList.toggle('hidden', !willOpen);
        });
    });

    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeAll();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initSidebar();
    initThemeToggle();
    initDropdowns();
});
