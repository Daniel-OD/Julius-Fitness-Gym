/** @studio Daniel-OD · https://github.com/Daniel-OD/Julius-Fitness-Gym */
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function debugThemeState(trigger, hypothesisId) {
    const programSection = document.querySelector('#program');
    const scheduleLabel = programSection?.querySelector('p.font-semibold');
    const labelStyle = scheduleLabel ? getComputedStyle(scheduleLabel) : null;
    const sectionStyle = programSection ? getComputedStyle(programSection) : null;

    // #region agent log
    fetch('http://127.0.0.1:7897/ingest/d6f991e9-d6f3-4d92-be2e-7338c0dd4be4', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '45e8fc' },
        body: JSON.stringify({
            sessionId: '45e8fc',
            location: 'app.js:debugThemeState',
            message: 'theme runtime state',
            data: {
                trigger,
                hypothesisId,
                htmlHasDark: document.documentElement.classList.contains('dark'),
                storedTheme: localStorage.getItem('theme'),
                scheduleTextColor: labelStyle?.color ?? null,
                scheduleSectionBg: sectionStyle?.backgroundColor ?? null,
                hasVignetteClass: !!document.querySelector('.jf-hero-vignette'),
            },
            timestamp: Date.now(),
        }),
    }).catch(() => {});
    // #endregion
}

function initThemeToggles() {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            debugThemeState('theme-toggle-click', 'H-B');
        });
    });
}

function initSidebar() {
    const sidebar = document.querySelector('[data-sidebar]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');
    const openButtons = document.querySelectorAll('[data-sidebar-toggle]');
    const closeButtons = document.querySelectorAll('[data-sidebar-close]');

    if (!sidebar) {
        return;
    }

    const desktopQuery = window.matchMedia('(min-width: 1024px)');

    const open = () => {
        if (desktopQuery.matches) {
            return;
        }

        sidebar.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const close = () => {
        sidebar.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openButtons.forEach((btn) => btn.addEventListener('click', open));
    closeButtons.forEach((btn) => btn.addEventListener('click', close));
    backdrop?.addEventListener('click', close);

    desktopQuery.addEventListener('change', (event) => {
        if (event.matches) {
            close();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
        }
    });
}

function initDropdowns() {
    document.querySelectorAll('[data-dropdown]').forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-dropdown-menu]');

        if (!trigger || !menu) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            menu.classList.toggle('hidden');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown-menu]').forEach((menu) => {
            menu.classList.add('hidden');
        });
    });
}

function initFormSubmitGuard() {
    document.querySelectorAll('form[data-jf-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitButtons = form.querySelectorAll('[type="submit"], button[data-jf-submit]');

            submitButtons.forEach((button) => {
                if (button.disabled) {
                    return;
                }

                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.dataset.jfLoading = 'true';

                if (!button.querySelector('[data-jf-spinner]')) {
                    const spinner = document.createElement('span');
                    spinner.dataset.jfSpinner = '';
                    spinner.className = 'inline-flex';
                    spinner.innerHTML =
                        '<svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 14.93-4.24"></path></svg>';
                    button.prepend(spinner);
                }
            });
        });
    });
}

function initReveal() {
    const revealElements = document.querySelectorAll('.jf-reveal');

    if (revealElements.length === 0) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        revealElements.forEach((el) => el.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.12 },
    );

    revealElements.forEach((el) => observer.observe(el));
}

document.addEventListener('DOMContentLoaded', () => {
    debugThemeState('dom-content-loaded', 'H-C');
    initThemeToggles();
    initSidebar();
    initDropdowns();
    initFormSubmitGuard();
    initReveal();
});
