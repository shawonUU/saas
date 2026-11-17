<script>
document.addEventListener('DOMContentLoaded', function () {
    // Accessible mobile sidebar; close on Escape, overlay and navigation.
    const sidebar = document.querySelector('.workspace-sidebar');
    const toggles = Array.from(document.querySelectorAll('[data-ws-sidebar-toggle]'));
    const sidebarOverlay = document.querySelector('[data-ws-sidebar-overlay]');
    function setSidebarOpen(open) {
        document.body.classList.toggle('ws-sidebar-open', open);
        toggles.forEach(button => button.setAttribute('aria-expanded', String(open)));
        sidebarOverlay?.setAttribute('aria-hidden', String(!open));
    }
    toggles.forEach(button => button.addEventListener('click', () => setSidebarOpen(!document.body.classList.contains('ws-sidebar-open'))));
    sidebarOverlay?.addEventListener('click', () => setSidebarOpen(false));
    sidebar?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => setSidebarOpen(false)));
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') setSidebarOpen(false);
    });
    const filterRow = document.getElementById('cfg-filter-row');
    const filterToggle = document.getElementById('cfg-toggle-filters');
    const key = 'cfg-filters:' + window.location.pathname;
    let filterVisible = true;
    try { filterVisible = localStorage.getItem(key) !== 'hidden'; } catch(e) {}
    function refreshFilters() {
        if (!filterRow || !filterToggle) return;
        filterRow.hidden = !filterVisible;
        filterToggle.setAttribute('aria-expanded', String(filterVisible));
        filterToggle.textContent = filterVisible ? <?php echo json_encode(t('Hide Column Filters', 'কলাম ফিল্টার লুকান'), 512) ?> : <?php echo json_encode(t('Show Column Filters', 'কলাম ফিল্টার দেখান'), 512) ?>;
    }
    refreshFilters();
    filterToggle?.addEventListener('click', () => {
        filterVisible = !filterVisible;
        try { localStorage.setItem(key, filterVisible ? 'shown' : 'hidden'); } catch(e) {}
        refreshFilters();
    });
    document.querySelectorAll('.cfg-delete-form').forEach(form => form.addEventListener('submit', e => {
        if (!confirm(<?php echo json_encode(t('Delete this record?', 'এই রেকর্ডটি মুছে ফেলবেন?'), 512) ?>)) e.preventDefault();
    }));

    const modal = document.getElementById('cfg-modal');
    if (!modal) return;
    // Crucial: remove from admin-main stacking context, so overlay covers header and sidebar.
    document.body.appendChild(modal);
    const form = document.getElementById('cfg-form');
    const method = document.getElementById('cfg-method');
    const mode = document.getElementById('cfg-form-mode');
    const editId = document.getElementById('cfg-edit-id');
    const title = document.getElementById('cfg-dialog-title');
    const fields = <?php echo json_encode(array_keys($fields), 15, 512) ?>;
    const records = <?php echo json_encode($recordPayload, 15, 512) ?>;
    const storeUrl = <?php echo json_encode($storeUrl, 15, 512) ?>;
    const updateTemplate = <?php echo json_encode($updateTemplate, 15, 512) ?>;
    const resourceTitle = <?php echo json_encode(session('portal_lang') === 'bn' ? $titleBn : $title, 15, 512) ?>;
    const createLabel = <?php echo json_encode(t('Create', 'নতুন'), 512) ?>;
    const editLabel = <?php echo json_encode(t('Edit', 'সম্পাদনা'), 512) ?>;
    let previousFocus = null;
    let previousOverflow = '';

    function openModal(recordId = null) {
        previousFocus = document.activeElement;
        form.reset();
        const row = recordId !== null ? records[String(recordId)] : null;
        mode.value = row ? 'edit' : 'create';
        editId.value = row ? String(recordId) : '';
        method.disabled = !row;
        form.action = row ? updateTemplate.replace('__ID__', encodeURIComponent(String(recordId))) : storeUrl;
        title.textContent = (row ? editLabel : createLabel) + ' ' + resourceTitle;
        if (row) {
            fields.forEach(field => {
                const input = form.elements.namedItem(field);
                if (input) input.value = row[field] == null ? '' : String(row[field] === true ? 1 : row[field] === false ? 0 : row[field]);
            });
            const status = form.elements.namedItem('is_active');
            if (status) status.value = row.is_active ? '1' : '0';
        }
        previousOverflow = document.body.style.overflow;
        modal.hidden = false;
        document.body.style.overflow = 'hidden';
        modal.querySelector('.cfg-dialog')?.scrollTo(0, 0);
        modal.querySelector('.cfg-dialog-close')?.focus();
    }
    function closeModal() {
        modal.hidden = true;
        document.body.style.overflow = previousOverflow;
        previousFocus?.focus?.();
    }
    document.getElementById('cfg-add')?.addEventListener('click', () => openModal());
    document.querySelectorAll('.cfg-edit').forEach(btn => btn.addEventListener('click', () => openModal(btn.dataset.id)));
    document.getElementById('cfg-close')?.addEventListener('click', closeModal);
    document.getElementById('cfg-cancel')?.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => {
        if (modal.hidden) return;
        if (e.key === 'Escape') { e.preventDefault(); closeModal(); }
        if (e.key === 'Tab') {
            const focusable = Array.from(modal.querySelectorAll('button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),a[href]'))
                .filter(el => el.offsetParent !== null);
            if (!focusable.length) return;
            const first = focusable[0], last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        }
    });
    const oldForm = <?php echo json_encode(old('_cfg_form'), 15, 512) ?>;
    const oldMode = <?php echo json_encode(old('form_mode'), 15, 512) ?>;
    const oldEditId = <?php echo json_encode(old('edit_id'), 15, 512) ?>;
    const oldData = <?php echo json_encode(old(), 15, 512) ?>;
    if (oldForm === '1' && <?php echo json_encode($errors->any(), 15, 512) ?>) {
        openModal(oldMode === 'edit' && records[String(oldEditId)] ? oldEditId : null);
        [...fields, 'is_active'].forEach(field => {
            const input = form.elements.namedItem(field);
            if (input && Object.prototype.hasOwnProperty.call(oldData, field)) input.value = String(oldData[field] ?? '');
        });
    }
});
</script>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/configurations/_ui_scripts.blade.php ENDPATH**/ ?>