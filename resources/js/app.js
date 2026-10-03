// UEST HRMS - Header & Navigation Interactivity

document.addEventListener('DOMContentLoaded', () => {
    // 1. Sidebar Toggle
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const navTexts = document.querySelectorAll('.nav-text');
    const logoText = document.getElementById('logoText');
    const userRoleFooter = document.getElementById('userRoleFooter');

    let isCollapsed = false;

    if (sidebarToggle && sidebar && mainContent) {
        sidebarToggle.addEventListener('click', () => {
            if (window.innerWidth >= 1024) {
                // Desktop Collapse
                isCollapsed = !isCollapsed;
                if (isCollapsed) {
                    sidebar.classList.remove('w-60');
                    sidebar.classList.add('w-16');
                    mainContent.classList.remove('ml-60');
                    mainContent.classList.add('ml-16');
                    navTexts.forEach(el => el.classList.add('hidden'));
                    if (logoText) logoText.classList.add('hidden');
                    if (userRoleFooter) userRoleFooter.classList.add('hidden');
                } else {
                    sidebar.classList.remove('w-16');
                    sidebar.classList.add('w-60');
                    mainContent.classList.remove('ml-16');
                    mainContent.classList.add('ml-60');
                    navTexts.forEach(el => el.classList.remove('hidden'));
                    if (logoText) logoText.classList.remove('hidden');
                    if (userRoleFooter) userRoleFooter.classList.remove('hidden');
                }
            } else {
                // Mobile Slide
                sidebar.classList.toggle('-translate-x-full');
            }
        });
    }

    // 2. Notification Dropdown Toggle
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');

    if (notificationBtn && notificationDropdown) {
        notificationBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (userDropdown) userDropdown.classList.add('hidden');
            if (themeDropdown) themeDropdown.classList.add('hidden');
            notificationDropdown.classList.toggle('hidden');
        });
    }

    // 3. User Profile Dropdown Toggle
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdown = document.getElementById('userDropdown');

    if (userMenuBtn && userDropdown) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (notificationDropdown) notificationDropdown.classList.add('hidden');
            if (themeDropdown) themeDropdown.classList.add('hidden');
            userDropdown.classList.toggle('hidden');
        });
    }

    // 4. Theme Color Combos Switcher Toggle
    const themeSwitcherBtn = document.getElementById('themeSwitcherBtn');
    const themeDropdown = document.getElementById('themeDropdown');

    if (themeSwitcherBtn && themeDropdown) {
        themeSwitcherBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (notificationDropdown) notificationDropdown.classList.add('hidden');
            if (userDropdown) userDropdown.classList.add('hidden');
            themeDropdown.classList.toggle('hidden');
        });
    }

    // 5. Close dropdowns on outside click or Escape key
    document.addEventListener('click', (e) => {
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target !== notificationBtn) {
            notificationDropdown.classList.add('hidden');
        }
        if (userDropdown && !userDropdown.contains(e.target) && e.target !== userMenuBtn) {
            userDropdown.classList.add('hidden');
        }
        if (themeDropdown && !themeDropdown.contains(e.target) && !themeSwitcherBtn.contains(e.target)) {
            themeDropdown.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (notificationDropdown) notificationDropdown.classList.add('hidden');
            if (userDropdown) userDropdown.classList.add('hidden');
            if (themeDropdown) themeDropdown.classList.add('hidden');
        }
    });

    // 6. Live Digital Clock
    const liveClock = document.getElementById('liveClock');
    if (liveClock) {
        const updateClock = () => {
            const now = new Date();
            let hours = now.getHours();
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 hour is 12
            liveClock.textContent = `${hours}:${minutes} ${ampm}`;
        };
        updateClock();
        setInterval(updateClock, 30000);
    }

    // 7. Theme Switcher System
    const updateThemeUI = (theme) => {
        // Topbar popover dropdown buttons
        document.querySelectorAll('.theme-option-btn').forEach(btn => {
            const isMatch = btn.dataset.themeTarget === theme;
            const check = btn.querySelector('.theme-check');
            if (check) {
                check.classList.toggle('opacity-0', !isMatch);
            }
            if (isMatch) {
                btn.classList.add('border-brand', 'bg-gray-50');
                btn.classList.remove('border-gray-200');
            } else {
                btn.classList.remove('border-brand', 'bg-gray-50');
                btn.classList.add('border-gray-200');
            }
        });

        // Profile page theme cards
        document.querySelectorAll('.theme-card').forEach(card => {
            const isMatch = card.dataset.themeTarget === theme;
            const check = card.querySelector('.theme-check');
            if (check) {
                check.classList.toggle('opacity-0', !isMatch);
            }
            if (isMatch) {
                card.classList.add('border-brand', 'bg-gray-50', 'ring-1', 'ring-black/10');
                card.classList.remove('border-gray-200');
            } else {
                card.classList.remove('border-brand', 'bg-gray-50', 'ring-1', 'ring-black/10');
                card.classList.add('border-gray-200');
            }
        });
    };

    window.applyTheme = function(theme, persist = true) {
        // Instant visual application
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('hrms_theme', theme);
        updateThemeUI(theme);

        // Optional sync to database
        if (persist) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                fetch('/profile/theme', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ theme: theme })
                }).catch(() => {
                    // Fail silently if offline or unauthenticated
                });
            }
        }
    };

    // Initialize UI on page load
    const currentTheme = document.documentElement.getAttribute('data-theme') || localStorage.getItem('hrms_theme') || 'charcoal';
    updateThemeUI(currentTheme);
});

