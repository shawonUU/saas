/* SaaS inline searchable native-select adapter V4 | 2026-11-28.
 * One editable combobox input, NO secondary search field. The original <select>
 * remains the source of truth for validation, form submission and existing JS.
 */
(() => {
  'use strict';
  if (window.NexaSearchSelect?.inlineVersion) return;
  const states = new WeakMap();
  const live = new Set();
  const lang = (document.documentElement.lang || 'en').toLowerCase().startsWith('bn') ? 'bn' : 'en';
  const words = lang === 'bn' ? {
    search: 'এখানেই লিখে খুঁজুন', empty: 'কোনো অপশন পাওয়া যায়নি', more: 'আরও অপশন দেখতে এখানে লিখে খুঁজুন',
    choose: 'নির্বাচন করুন', options: 'অপশন দেখান'
  } : {
    search: 'Type here to search', empty: 'No matching options', more: 'Type to narrow the results',
    choose: 'Select an option', options: 'Show options'
  };
  const maxVisible = 160;
  let sequence = 0;
  let active = null;
  let pendingPosition = false;
  const clean = value => String(value ?? '').normalize('NFKC').toLocaleLowerCase().trim();

  function nativeText(select) {
    const opt = select.selectedOptions[0];
    if (!opt || opt.value === '') return '';
    return opt.textContent.trim().replace(/\s+/g, ' ');
  }
  function nativePlaceholder(select) {
    return select.getAttribute('data-placeholder') || select.querySelector('option[value=""]')?.textContent.trim() || words.choose;
  }
  function isUsable(select) {
    return select instanceof HTMLSelectElement && !select.multiple && select.size <= 1 && !select.matches('[data-native-select],[data-search-select="off"]');
  }
  function sourceOptions(state) {
    return Array.from(state.select.options).map((option, index) => ({
      index, option, value: option.value, label: option.textContent.trim().replace(/\s+/g, ' '),
      disabled: option.disabled || Boolean(option.closest('optgroup[disabled]')),
      hidden: option.hidden || Boolean(option.closest('optgroup[hidden]')),
      group: option.parentElement?.tagName === 'OPTGROUP' ? option.parentElement.label : ''
    })).filter(item => !item.hidden);
  }
  function close() {
    if (!active) return;
    const state = active; active = null;
    if (state.menu.matches(':popover-open')) state.menu.hidePopover();
    state.menu.classList.remove('saas-combo-menu-fallback');
    state.menu.hidden = true;
    state.input.setAttribute('aria-expanded', 'false');
    state.input.removeAttribute('aria-activedescendant');
    state.highlight = -1;
    if (state.select.isConnected) sync(state);
  }
  function sync(state, preserveQuery = false) {
    if (!state.select.isConnected) return;
    state.input.disabled = state.select.disabled;
    state.input.required = false; // Native select validates; its invalid handler focuses the visible input.
    state.input.placeholder = nativePlaceholder(state.select);
    state.input.setAttribute('aria-required', String(state.select.required));
    if (!preserveQuery) state.input.value = nativeText(state.select);
    state.host.classList.toggle('saas-combo-disabled', state.select.disabled);
    if (state.select.disabled && active === state) close();
    if (active === state) render(state, state.input.value === nativeText(state.select) ? '' : state.input.value);
  }
  function position() {
    pendingPosition = false;
    const state = active;
    if (!state || !state.input.isConnected) { close(); return; }
    const rect = state.input.getBoundingClientRect();
    const view = window.visualViewport;
    const vTop = view?.offsetTop || 0;
    const vHeight = view?.height || window.innerHeight;
    const vBottom = vTop + vHeight;
    const vWidth = view?.width || window.innerWidth;
    if (rect.bottom < vTop || rect.top > vBottom) { close(); return; }
    const spaceBelow = Math.max(0, vBottom - rect.bottom - 10);
    const spaceAbove = Math.max(0, rect.top - vTop - 10);
    const above = spaceBelow < 165 && spaceAbove > spaceBelow;
    const maxHeight = Math.max(80, Math.min(300, (above ? spaceAbove : spaceBelow) - 3));
    const width = Math.min(Math.max(rect.width, 155), Math.max(155, vWidth - 16));
    const left = Math.max(8, Math.min(rect.left, vWidth - width - 8));
    const preferredHeight = Math.min(state.menu.scrollHeight || 180, maxHeight);
    const top = above ? Math.max(vTop + 6, rect.top - preferredHeight - 5) : Math.min(vBottom - 30, rect.bottom + 5);
    // Important: the V3 stylesheet declared `inset:auto!important`,
    // which overrode JS top/left; the menu appeared at viewport (0, 0).
    // Explicit CSSOM priorities guard against page-specific !important overrides.
    state.menu.style.setProperty('left', left + 'px', 'important');
    state.menu.style.setProperty('top', top + 'px', 'important');
    state.menu.style.setProperty('width', width + 'px', 'important');
    state.menu.style.setProperty('max-height', maxHeight + 'px', 'important');
    state.menu.style.setProperty('right', 'auto', 'important');
    state.menu.style.setProperty('bottom', 'auto', 'important');
    state.menu.classList.toggle('saas-combo-above', above);
  }
  function queuePosition() {
    if (!pendingPosition && active) {
      pendingPosition = true;
      requestAnimationFrame(position);
    }
  }
  function highlight(state, index, scroll = true) {
    const candidates = state.visible;
    if (!candidates.length) { state.highlight = -1; return; }
    const next = Math.max(0, Math.min(index, candidates.length - 1));
    state.highlight = next;
    state.menu.querySelectorAll('[role="option"]').forEach(el => {
      const chosen = Number(el.dataset.row) === next;
      el.classList.toggle('is-active', chosen);
      if (chosen) state.input.setAttribute('aria-activedescendant', el.id);
    });
    const current = state.menu.querySelector('[data-row="' + next + '"]');
    if (scroll && current) current.scrollIntoView({ block: 'nearest' });
  }
  function render(state, query = '') {
    const key = clean(query);
    const items = sourceOptions(state).filter(item => !key || (item.value !== '' && (clean(item.label).includes(key) || clean(item.group).includes(key))));
    state.visible = items.filter(item => !item.disabled).slice(0, maxVisible);
    state.menu.replaceChildren();
    state.highlight = -1;
    let previousGroup = null;
    if (!items.length) {
      const empty = document.createElement('div');
      empty.className = 'saas-combo-empty'; empty.textContent = words.empty;
      state.menu.append(empty);
    } else {
      for (const item of items.slice(0, maxVisible)) {
        if (item.group && item.group !== previousGroup) {
          const heading = document.createElement('div');
          heading.className = 'saas-combo-group'; heading.textContent = item.group;
          state.menu.append(heading);
        }
        previousGroup = item.group;
        const row = document.createElement('div');
        row.className = 'saas-combo-option';
        row.id = state.menu.id + '-option-' + item.index;
        row.setAttribute('role', 'option');
        row.setAttribute('aria-selected', String(item.index === state.select.selectedIndex));
        row.dataset.index = String(item.index);
        row.dataset.row = String(state.visible.findIndex(x => x.index === item.index));
        row.setAttribute('aria-disabled', String(item.disabled));
        const label = document.createElement('span'); label.textContent = item.label;
        row.append(label);
        if (item.index === state.select.selectedIndex) {
          const check = document.createElement('span');
          check.className = 'saas-combo-check'; check.textContent = '✓'; check.setAttribute('aria-hidden', 'true');
          row.append(check);
        }
        state.menu.append(row);
      }
      if (items.length > maxVisible) {
        const more = document.createElement('div');
        more.className = 'saas-combo-empty'; more.textContent = words.more;
        state.menu.append(more);
      }
    }
    const selected = state.visible.findIndex(item => item.index === state.select.selectedIndex);
    if (selected >= 0) highlight(state, selected, false);
    queuePosition();
  }
  function open(state, query = '') {
    if (state.select.disabled || !state.select.isConnected) return;
    if (active !== state) close();
    active = state;
    // Native modal dialogs make outside DOM inert. Portal inside the dialog,
    // then use popover top-layer so scroll containers cannot clip results.
    const portalRoot = state.select.closest("dialog[open]") || document.body;
    if (state.menu.parentElement !== portalRoot) portalRoot.append(state.menu);
    state.menu.hidden = false;
    render(state, query);
    if (typeof state.menu.showPopover === 'function') {
      try { state.menu.showPopover(); }
      catch { state.menu.classList.add('saas-combo-menu-fallback'); }
    } else state.menu.classList.add('saas-combo-menu-fallback');
    state.input.setAttribute('aria-expanded', 'true');
    position();
  }
  function commit(state, index) {
    const opt = state.select.options[index];
    if (!opt || opt.disabled || opt.closest('optgroup[disabled]')) return;
    const changed = state.select.selectedIndex !== index;
    state.select.selectedIndex = index;
    close();
    state.input.value = nativeText(state.select);
    state.input.focus({ preventScroll: true });
    if (changed) {
      state.select.dispatchEvent(new Event('input', { bubbles: true }));
      state.select.dispatchEvent(new Event('change', { bubbles: true }));
    }
  }
  function enhance(select) {
    if (!isUsable(select)) return;
    if (states.has(select)) { sync(states.get(select), active?.select === select && document.activeElement === states.get(select).input); return; }
    const host = document.createElement('span');
    host.className = 'saas-combo';
    const input = document.createElement('input');
    input.type = 'text';
    input.className = (select.className ? select.className + ' ' : '') + 'saas-combo-input';
    input.autocomplete = 'off'; input.spellcheck = false;
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-haspopup', 'listbox');
    input.setAttribute('aria-expanded', 'false');
    input.id = 'saas-inline-combo-' + (++sequence);
    input.setAttribute('aria-label', select.getAttribute('aria-label') || select.labels?.[0]?.textContent.trim() || select.name || words.search);
    input.setAttribute('title', words.search);
    const menu = document.createElement('div');
    menu.className = 'saas-combo-menu';
    menu.setAttribute('popover', 'manual');
    menu.id = 'saas-inline-options-' + sequence;
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;
    input.setAttribute('aria-controls', menu.id);
    // The native select must remain the submitted/validated element, not a second form field.
    select.classList.add('saas-combo-native');
    select.tabIndex = -1;
    select.insertAdjacentElement('afterend', host);
    host.append(input);
    document.body.append(menu);
    const state = { select, host, input, menu, visible: [], highlight: -1, observer: null };
    states.set(select, state); live.add(state);
    sync(state);
    select.addEventListener('change', () => sync(state, false));
    select.addEventListener('input', () => sync(state, false));
    select.addEventListener('invalid', event => {
      event.preventDefault(); input.focus(); open(state); input.setAttribute('aria-invalid', 'true');
    });
    const label = select.labels?.[0];
    if (label) {
      label.addEventListener('click', event => {
        if (!event.target.closest?.('.saas-combo')) {
          event.preventDefault(); input.focus();
        }
      });
    }
    input.addEventListener('focus', () => {
      sync(state);
      // Typing replaces the existing selection immediately, with no secondary search box.
      input.select();
      open(state);
    });
    input.addEventListener('click', () => {
      if (active !== state) open(state);
      if (input.value === nativeText(state.select)) input.select();
    });
    input.addEventListener('input', () => {
      input.removeAttribute('aria-invalid');
      if (active !== state) open(state, input.value);
      else render(state, input.value);
    });
    input.addEventListener('keydown', event => {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault(); event.stopPropagation();
        if (active !== state) open(state);
        highlight(state, state.highlight + (event.key === 'ArrowDown' ? 1 : -1));
      } else if (event.key === 'Enter') {
        if (active !== state) return;
        event.preventDefault(); event.stopPropagation();
        const item = state.visible[state.highlight] || (state.visible.length === 1 ? state.visible[0] : null);
        if (item) commit(state, item.index);
      } else if (event.key === 'Escape' && active === state) {
        event.preventDefault(); event.stopPropagation(); close();
      } else if (event.key === 'Tab') close();
    });
    input.addEventListener('blur', () => {
      // Pointer selection uses preventDefault() to keep focus on the combobox.
      queueMicrotask(() => { if (active === state && document.activeElement !== input) close(); });
    });
    menu.addEventListener('pointerdown', event => {
      if (event.pointerType === 'mouse' && event.target.closest('[role="option"]')) event.preventDefault();
    });
    menu.addEventListener('click', event => {
      const row = event.target.closest('[role="option"]');
      if (!row || row.getAttribute('aria-disabled') === 'true') return;
      commit(state, Number(row.dataset.index));
    });
    state.observer = new MutationObserver(() => {
      if (active === state) render(state, state.input.value === nativeText(select) ? '' : state.input.value);
      else sync(state);
    });
    state.observer.observe(select, { subtree: true, childList: true, characterData: true, attributes: true,
      attributeFilter: ['disabled', 'selected', 'hidden', 'label', 'value'] });
    select.form?.addEventListener('reset', () => setTimeout(() => sync(state), 0));
  }
  function refresh(root = document) {
    if (typeof root === 'string') root = document.querySelector(root);
    if (!root) return;
    if (root.matches?.('select')) enhance(root);
    root.querySelectorAll?.('select').forEach(enhance);
    if (root !== document) for (const state of live) {
      if (root === state.select || root.contains?.(state.select)) sync(state, active === state);
    }
  }
  function sweep() {
    for (const state of live) {
      if (state.select.isConnected) continue;
      if (active === state) close();
      state.observer.disconnect(); state.host.remove(); state.menu.remove(); live.delete(state);
    }
  }
  const start = () => {
    refresh(document);
    const observer = new MutationObserver(mutations => {
      const changed = new Set();
      let removed = false;
      for (const mutation of mutations) {
        if (mutation.type === 'attributes') {
          const el = mutation.target;
          if (el.matches?.('select')) changed.add(el);
          if (el === active?.select || el.contains?.(active?.select)) queuePosition();
        } else {
          for (const node of mutation.addedNodes) {
            if (node.nodeType !== 1) continue;
            if (node.matches?.('select')) changed.add(node);
            node.querySelectorAll?.('select').forEach(sel => changed.add(sel));
          }
          for (const node of mutation.removedNodes) if (node.nodeType === 1 && (node.matches?.('select') || node.querySelector?.('select'))) removed = true;
        }
      }
      for (const select of changed) if (select.isConnected) enhance(select);
      if (removed) sweep();
      if (active && !active.input.getClientRects().length) close();
    });
    observer.observe(document.documentElement, { childList: true, subtree: true, attributes: true,
      attributeFilter: ['disabled', 'hidden'] });
    document.addEventListener('pointerdown', event => {
      if (active && !active.host.contains(event.target) && !active.menu.contains(event.target)) close();
    }, true);
    document.addEventListener('focusin', event => {
      if (active && event.target !== active.input && !active.menu.contains(event.target)) close();
    });
    document.addEventListener('scroll', queuePosition, true);
    window.addEventListener('resize', queuePosition);
    window.visualViewport?.addEventListener('resize', queuePosition);
    window.visualViewport?.addEventListener('scroll', queuePosition);
  };
  window.NexaSearchSelect = { inlineVersion: '4.0.0', refresh, close, open: select => {
    const state = states.get(select); if (state) { state.input.focus(); open(state); }
  }};
  window.SaasInlineSelect = window.NexaSearchSelect;
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start, { once: true });
  else start();
})();
