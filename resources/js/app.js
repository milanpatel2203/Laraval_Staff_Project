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
            userDropdown.classList.toggle('hidden');
        });
    }

    // 4. Close dropdowns on outside click or Escape key
    document.addEventListener('click', (e) => {
        if (notificationDropdown && !notificationDropdown.contains(e.target) && e.target !== notificationBtn) {
            notificationDropdown.classList.add('hidden');
        }
        if (userDropdown && !userDropdown.contains(e.target) && e.target !== userMenuBtn) {
            userDropdown.classList.add('hidden');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (notificationDropdown) notificationDropdown.classList.add('hidden');
            if (userDropdown) userDropdown.classList.add('hidden');
        }
    });

    // 5. Live Digital Clock
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
});
