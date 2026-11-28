<script>
(function () {
    'use strict';
    const root = document.getElementById('saas-header-actions');
    if (!root) return;
    const profile = document.getElementById('saas-profile-menu');
    const profileButton = document.getElementById('saas-profile-trigger');
    const notifications = document.getElementById('saas-notif-menu');
    const notificationButton = document.getElementById('saas-notif-trigger');
    const list = document.getElementById('saas-notif-list');
    const count = document.getElementById('saas-notif-count');
    const description = document.getElementById('saas-notif-description');
    const readAll = document.getElementById('saas-notif-read-all');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const themeState = document.getElementById('saas-profile-theme-mode');
    const syncTheme = () => { if (themeState) themeState.textContent = document.documentElement.dataset.theme === 'dark' ? <?php echo json_encode(t('Dark', 'ডার্ক'), 512) ?> : <?php echo json_encode(t('Light', 'লাইট'), 512) ?>; };
    document.addEventListener('saas:theme-change', syncTheme);
    syncTheme();
    const labels = {
        loading: <?php echo json_encode(t('Loading notifications…', 'নোটিফিকেশন লোড হচ্ছে…'), 512) ?>,
        empty: <?php echo json_encode(t('All caught up!', 'নতুন কোনো নোটিফিকেশন নেই!'), 512) ?>,
        emptyText: <?php echo json_encode(t('You have no notifications yet.', 'এখনো কোনো নোটিফিকেশন আসেনি।'), 512) ?>,
        error: <?php echo json_encode(t('Could not load notifications. Try again.', 'নোটিফিকেশন লোড হয়নি। আবার চেষ্টা করুন।'), 512) ?>,
        setup: <?php echo json_encode(t('Run database migrations to enable notifications.', 'নোটিফিকেশন চালু করতে ডাটাবেস মাইগ্রেশন চালান।'), 512) ?>,
        unread: <?php echo json_encode(t('Unread notifications', 'অপঠিত নোটিফিকেশন'), 512) ?>
    };
    function close(menu, trigger) {
        if (menu) menu.hidden = true;
        trigger?.setAttribute('aria-expanded', 'false');
    }
    function show(menu, trigger, otherMenu, otherTrigger) {
        if (!menu || !trigger) return;
        const open = menu.hidden;
        close(otherMenu, otherTrigger);
        menu.hidden = !open;
        trigger.setAttribute('aria-expanded', String(open));
        if (open && menu === notifications) loadNotifications();
    }
    profileButton?.addEventListener('click', () => show(profile, profileButton, notifications, notificationButton));
    notificationButton?.addEventListener('click', () => show(notifications, notificationButton, profile, profileButton));
    document.addEventListener('click', event => {
        if (!document.getElementById('saas-profile-anchor')?.contains(event.target)) close(profile, profileButton);
        if (!document.getElementById('saas-notif-anchor')?.contains(event.target)) close(notifications, notificationButton);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') { close(profile, profileButton); close(notifications, notificationButton); }
    });

    const passwordDialog = document.getElementById('saas-password-dialog');
    const openPassword = () => {
        close(profile, profileButton);
        if (passwordDialog && !passwordDialog.open) passwordDialog.showModal();
    };
    document.getElementById('saas-password-open')?.addEventListener('click', openPassword);
    document.getElementById('saas-password-close')?.addEventListener('click', () => passwordDialog?.close());
    document.getElementById('saas-password-cancel')?.addEventListener('click', () => passwordDialog?.close());
    passwordDialog?.addEventListener('click', event => { if (event.target === passwordDialog) passwordDialog.close(); });
    if (<?php echo json_encode($errors->getBag('profilePassword')->any(), 15, 512) ?>) openPassword();

    const create = (tag, className, value) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (value !== undefined) node.textContent = value;
        return node;
    };
    function empty(message, subtitle) {
        if (!list) return;
        list.replaceChildren();
        const box = create('div', 'saas-notif-empty');
        const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        icon.setAttribute('viewBox', '0 0 24 24');
        icon.setAttribute('fill', 'none');
        icon.setAttribute('stroke', 'currentColor');
        icon.setAttribute('stroke-width', '1.7');
        icon.innerHTML = '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/>';
        box.append(icon, create('strong', '', message));
        if (subtitle) box.append(create('span', '', subtitle));
        list.append(box);
    }
    function badge(unread) {
        const total = Math.max(0, Number(unread) || 0);
        if (count) { count.hidden = total === 0; count.textContent = total > 99 ? '99+' : String(total); }
        if (readAll) readAll.hidden = total === 0;
        notificationButton?.setAttribute('aria-label', total ? labels.unread + ': ' + total : <?php echo json_encode(t('Notifications', 'নোটিফিকেশন'), 512) ?>);
    }
    async function request(url, method) {
        const response = await fetch(url, {method, credentials:'same-origin', headers:{Accept:'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':csrf}});
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    }
    let loading = false;
    async function loadNotifications() {
        if (loading || !list) return;
        loading = true;
        if (!list.children.length) empty(labels.loading);
        try {
            const data = await request(root.dataset.notificationUrl, 'GET');
            badge(data.unread);
            list.replaceChildren();
            if (data.setup_required) { empty(labels.setup); return; }
            if (!Array.isArray(data.items) || !data.items.length) { empty(labels.empty, labels.emptyText); return; }
            for (const item of data.items) {
                const button = create('button', 'saas-notif-item' + (item.unread ? ' is-unread' : ''));
                button.type = 'button';
                const symbol = create('span', 'saas-notif-symbol');
                symbol.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/></svg>';
                const copy = create('span', 'saas-notif-copy');
                copy.append(create('strong', '', item.title || <?php echo json_encode(t('Notification', 'নোটিফিকেশন'), 512) ?>));
                if (item.message) copy.append(create('p', '', item.message));
                copy.append(create('time', '', item.time || ''));
                button.append(symbol, copy);
                if (item.unread) button.append(create('span', 'saas-unread-dot'));
                button.addEventListener('click', async () => {
                    if (item.unread) {
                        try { await request(item.read_url, 'POST'); } catch (_) { empty(labels.error); return; }
                    }
                    if (item.url) {
                        try {
                            const target = new URL(item.url, location.origin);
                            if (target.origin === location.origin && /^https?:$/.test(target.protocol)) {
                                location.assign(target.href);
                                return;
                            }
                        } catch (_) {}
                    }
                    await loadNotifications();
                });
                list.append(button);
            }
        } catch (_) { empty(labels.error); }
        finally { loading = false; }
    }
    readAll?.addEventListener('click', async () => {
        readAll.disabled = true;
        try { await request(root.dataset.notificationReadAll, 'POST'); await loadNotifications(); }
        catch (_) { empty(labels.error); }
        finally { readAll.disabled = false; }
    });
    if (notificationButton) {
        loadNotifications();
        setInterval(() => { if (!document.hidden) loadNotifications(); }, 60000);
    }
})();
</script>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/settings/business/profile/partials/header-script.blade.php ENDPATH**/ ?>