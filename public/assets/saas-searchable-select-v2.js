/* SaaS shared searchable select v2: native form values, accessible search, top-layer results. */
(() => {
  'use strict';
  if (window.NexaSearchSelect?.version === '2026.11.28') return;

  const instances = new WeakMap();
  const activeInstances = new Set();
  const maxRows = 160;
  let active = null;
  let popup, search, results, count, currentRows = [], highlighted = -1;
  let positionPending = false;
  let mutationPending = false;
  let popupVisible = false;

  const isBn = () => (document.documentElement.lang || '').toLowerCase().startsWith('bn');
  const words = (en, bn) => isBn() ? bn : en;
  const normalize = value => String(value ?? '').normalize('NFKC').toLocaleLowerCase().trim();
  const el = (tag, className = '', text = '') => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text) node.textContent = text;
    return node;
  };
  const eligible = select => {
    if (!(select instanceof HTMLSelectElement)) return false;
    if (select.multiple || select.size > 1 || select.closest('template') || select.hidden || select.style.display === 'none') return false;
    if (select.matches('[data-no-search],[data-native-select],[data-nxs-ignore],.nxs-ignore') || select.dataset.searchable === 'false' || select.dataset.searchable === '0') return false;
    if (select.hasAttribute('data-searchable') || select.matches('.searchable-select,.js-searchable,.select-search')) return true;
    if (select.matches('[name="per_page"],[name="page_size"],[name="limit"]') || select.closest('.cfg-toolbar,.cfg-filter-row,.listui-controls,.ret-toolbar')) return false;
    return [...select.options].filter(option => option.value && !option.disabled).length >= 4;
  };
  const titleFor = select => {
    if (select.getAttribute('aria-label')) return select.getAttribute('aria-label');
    if (select.id) {
      const label = [...document.querySelectorAll('label[for]')].find(l => l.htmlFor === select.id);
      if (label) return label.textContent.trim();
    }
    return select.closest('label')?.textContent?.trim() || select.name || words('Choose an option', 'একটি অপশন বেছে নিন');
  };
  const displayText = select => {
    const selected = select.selectedOptions[0];
    if (selected && selected.value) return selected.textContent.trim();
    return select.dataset.placeholder || select.options[0]?.textContent?.trim() || words('Select an option', 'অপশন নির্বাচন করুন');
  };

  function ensurePopup() {
    if (popup) return;
    popup = el('div', 'sxs-popover');
    popup.id = 'sxs-options';
    popup.setAttribute('popover', 'manual');
    popup.hidden = true;
    popup.setAttribute('aria-label', words('Searchable options', 'সার্চ করা অপশন'));
    const field = el('div', 'sxs-search-area');
    search = el('input', 'sxs-search-input');
    search.type = 'search';
    search.autocomplete = 'off';
    search.spellcheck = false;
    search.placeholder = words('Search options…', 'অপশন খুঁজুন…');
    search.setAttribute('aria-label', words('Search options', 'অপশন খুঁজুন'));
    field.appendChild(search);
    results = el('div', 'sxs-result-list');
    results.setAttribute('role', 'listbox');
    results.id = 'sxs-result-list';
    count = el('div', 'sxs-count');
    count.setAttribute('role', 'status');
    count.setAttribute('aria-live', 'polite');
    popup.append(field, results, count);
    document.body.appendChild(popup);
    search.addEventListener('input', () => render());
    search.addEventListener('keydown', event => {
      if (!active) return;
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        if (currentRows.length) highlight((highlighted + (event.key === 'ArrowDown' ? 1 : -1) + currentRows.length) % currentRows.length);
      } else if (event.key === 'Enter') {
        event.preventDefault();
        if (highlighted >= 0 && currentRows[highlighted]) choose(highlighted);
      } else if (event.key === 'Tab') close(false);
    });
    results.addEventListener('pointerdown', event => {
      if (event.target.closest('[data-sxs-row]')) event.preventDefault();
    });
    results.addEventListener('click', event => {
      const row = event.target.closest('[data-sxs-row]');
      if (row) choose(Number(row.dataset.sxsRow));
    });
  }

  function optionItems(select) {
    return [...select.options].flatMap((option, index) => {
      const group = option.parentElement?.tagName === 'OPTGROUP' ? option.parentElement : null;
      if ((!option.value && select.required) || option.disabled || option.hidden || group?.disabled || group?.hidden) return [];
      const label = option.textContent.trim();
      const extra = Object.values(option.dataset || {}).join(' ');
      return [{ index, label, group: group?.label || '', search: normalize([label, group?.label, extra].join(' ')), selected: option.selected }];
    });
  }

  function highlight(index) {
    highlighted = index;
    [...results.querySelectorAll('[data-sxs-row]')].forEach((button, i) => {
      button.classList.toggle('sxs-highlighted', i === index);
      button.setAttribute('aria-current', String(i === index));
      if (i === index) {
        search.setAttribute('aria-activedescendant', button.id);
        button.scrollIntoView({ block: 'nearest' });
      }
    });
    if (index < 0) search.removeAttribute('aria-activedescendant');
  }

  function render() {
    if (!active) return;
    const fragment = document.createDocumentFragment();
    const query = normalize(search.value);
    const terms = query.split(/\s+/u).filter(Boolean);
    const available = optionItems(active.select);
    const matches = available.filter(item => terms.every(term => item.search.includes(term)));
    currentRows = matches.slice(0, maxRows);
    results.replaceChildren();
    let previousGroup = null;
    currentRows.forEach((item, i) => {
      if (item.group && item.group !== previousGroup) fragment.appendChild(el('div', 'sxs-group', item.group));
      previousGroup = item.group;
      const button = el('button', 'sxs-item', item.label);
      button.type = 'button';
      button.setAttribute('role', 'option');
      button.dataset.sxsRow = String(i);
      button.id = 'sxs-option-' + i;
      button.setAttribute('aria-selected', String(item.selected));
      if (item.selected) button.classList.add('sxs-selected');
      fragment.appendChild(button);
    });
    if (!matches.length) fragment.appendChild(el('div', 'sxs-empty', words('No matching options', 'কোনো অপশন পাওয়া যায়নি')));
    results.appendChild(fragment);
    count.textContent = matches.length > maxRows
      ? words(`Showing ${maxRows} of ${matches.length}. Type to narrow the search.`, `${matches.length}টির মধ্যে ${maxRows}টি দেখাচ্ছে। আরও লিখে খুঁজুন।`)
      : words(`${matches.length} option${matches.length === 1 ? '' : 's'}`, `${matches.length}টি অপশন`);
    const selected = currentRows.findIndex(item => item.selected);
    highlight(selected >= 0 ? selected : currentRows.length ? 0 : -1);
  }

  function update(inst) {
    if (!inst.select.isConnected) { destroy(inst); return; }
    inst.trigger.disabled = inst.select.disabled;
    inst.trigger.setAttribute('aria-disabled', String(inst.select.disabled));
    inst.trigger.textContent = displayText(inst.select);
    inst.trigger.classList.toggle('sxs-placeholder', !inst.select.value);
    if (active === inst) render();
  }

  function attach(select) {
    if (!eligible(select) || instances.has(select)) return;
    const wrapper = el('div', 'nxs-select sxs-select');
    wrapper.dataset.sxsEnhanced = '1';
    const trigger = el('button', 'sxs-trigger');
    trigger.type = 'button';
    trigger.setAttribute('role', 'combobox');
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-controls', 'sxs-result-list');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-label', titleFor(select));
    select.before(wrapper);
    wrapper.appendChild(select);
    wrapper.appendChild(trigger);
    select.classList.add('sxs-native');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');
    const inst = { select, wrapper, trigger };
    instances.set(select, inst);
    activeInstances.add(inst);
    update(inst);
    trigger.addEventListener('click', () => active === inst ? close(true) : open(inst));
    trigger.addEventListener('keydown', event => {
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') { event.preventDefault(); open(inst); }
    });
    select.addEventListener('change', () => update(inst));
  }

  function destroy(inst) {
    if (active === inst) close(false);
    activeInstances.delete(inst);
    instances.delete(inst.select);
  }

  function popupShow(inst) {
    ensurePopup();
    let canPopover = false;
    const dialog = inst.wrapper.closest('dialog[open]');
    const portalRoot = dialog || document.body;
    if (popup.parentElement !== portalRoot) portalRoot.appendChild(popup);
    popup.hidden = false;
    try {
      if (typeof popup.showPopover === 'function') { popup.showPopover(); canPopover = true; }
    } catch (_) { /* a browser without top-layer popovers uses a modal-safe fallback */ }
    if (!canPopover) {
      portalRoot.appendChild(popup);
      popup.classList.add('sxs-fallback');
      popup.hidden = false;
    } else popup.classList.remove('sxs-fallback');
    popupVisible = true;
  }

  function position() {
    positionPending = false;
    if (!active || !popupVisible) return;
    const anchor = active.trigger.getBoundingClientRect();
    const viewport = window.visualViewport;
    const vh = viewport?.height || window.innerHeight;
    const vw = viewport?.width || window.innerWidth;
    const viewTop = viewport?.offsetTop || 0;
    const viewLeft = viewport?.offsetLeft || 0;
    if (!anchor.width || !anchor.height || anchor.bottom < viewTop || anchor.top > viewTop + vh) { close(false); return; }
    const width = Math.min(Math.max(anchor.width, 230), Math.max(160, vw - 16));
    const left = Math.max(viewLeft + 8, Math.min(anchor.left, viewLeft + vw - width - 8));
    const below = viewTop + vh - anchor.bottom - 12;
    const above = anchor.top - viewTop - 12;
    const desired = 340;
    const upside = below < 200 && above > below;
    const available = Math.max(125, upside ? above : below);
    const maxHeight = Math.min(desired, available);
    popup.style.width = `${width}px`;
    popup.style.maxHeight = `${maxHeight}px`;
    popup.style.left = `${left}px`;
    popup.style.top = upside ? `${Math.max(viewTop + 8, anchor.top - maxHeight - 6)}px` : `${Math.max(viewTop + 8, anchor.bottom + 6)}px`;
    popup.classList.toggle('sxs-above', upside);
  }

  function schedulePosition() {
    if (positionPending || !active) return;
    positionPending = true;
    requestAnimationFrame(position);
  }

  function open(inst) {
    if (inst.select.disabled || !inst.trigger.isConnected) return;
    if (active) close(false);
    active = inst;
    inst.trigger.setAttribute('aria-expanded', 'true');
    popupShow(inst);
    search.value = '';
    search.placeholder = words('Search options…', 'অপশন খুঁজুন…');
    render();
    position();
    search.focus({ preventScroll: true });
  }

  function close(restoreFocus = true) {
    if (!active) return;
    const inst = active;
    inst.trigger.setAttribute('aria-expanded', 'false');
    active = null;
    popupVisible = false;
    if (popup) {
      try { if (typeof popup.hidePopover === 'function' && popup.matches(':popover-open')) popup.hidePopover(); } catch (_) {}
      popup.hidden = true;
      popup.classList.remove('sxs-fallback');
    }
    if (restoreFocus && inst.trigger.isConnected) inst.trigger.focus({ preventScroll: true });
  }

  function choose(index) {
    const item = currentRows[index];
    if (!active || !item) return;
    const native = active.select;
    native.selectedIndex = item.index;
    close(true);
    native.dispatchEvent(new Event('input', { bubbles: true }));
    native.dispatchEvent(new Event('change', { bubbles: true }));
    instances.get(native) && update(instances.get(native));
  }

  function refresh(root = document) {
    if (root instanceof HTMLSelectElement) attach(root);
    else if (root?.querySelectorAll) root.querySelectorAll('select').forEach(attach);
    for (const inst of [...activeInstances]) {
      if (!inst.select.isConnected) destroy(inst);
      else if (root === document || root === inst.select || root?.contains?.(inst.select)) update(inst);
    }
  }

  function watch() {
    const observer = new MutationObserver(records => {
      if (mutationPending) return;
      if (active && !active.trigger.getClientRects().length) close(false);
      const affectsSelect = records.some(record => {
        if (record.type === 'attributes') return record.target.matches?.('select,option,optgroup');
        if (record.target.matches?.('select,optgroup')) return true;
        return [...record.addedNodes, ...record.removedNodes].some(node => node.nodeType === 1 && (node.matches('select,option,optgroup') || node.querySelector('select,option,optgroup')));
      });
      if (!affectsSelect) return;
      mutationPending = true;
      requestAnimationFrame(() => { mutationPending = false; refresh(); });
    });
    observer.observe(document.body, {
      childList: true, subtree: true, attributes: true,
      attributeFilter: ['disabled', 'hidden', 'open', 'selected', 'label']
    });
  }

  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape' || !active) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    close(true);
  }, true);
  document.addEventListener('pointerdown', event => {
    if (!active) return;
    if (popup?.contains(event.target) || active.wrapper.contains(event.target)) return;
    close(false);
  }, true);
  document.addEventListener('invalid', event => {
    if (event.target instanceof HTMLSelectElement && instances.has(event.target)) {
      event.preventDefault();
      open(instances.get(event.target));
    }
  }, true);
  document.addEventListener('reset', event => requestAnimationFrame(() => refresh(event.target)), true);
  document.addEventListener('change', event => {
    if (event.target instanceof HTMLSelectElement) requestAnimationFrame(() => refresh(event.target.closest('form') || document));
  });
  document.addEventListener('click', event => {
    const label = event.target.closest?.('label[for]');
    if (!label || event.target.closest('button,input,select,textarea,a')) return;
    const target = document.getElementById(label.htmlFor);
    const inst = instances.get(target);
    if (inst && !target.disabled) { event.preventDefault(); open(inst); }
  });
  document.addEventListener('scroll', schedulePosition, true);
  window.addEventListener('resize', schedulePosition, { passive: true });
  window.visualViewport?.addEventListener('resize', schedulePosition, { passive: true });
  window.NexaSearchSelect = { version: '2026.11.28', refresh, init: refresh, close };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => { refresh(); watch(); }, { once: true });
  else { refresh(); watch(); }
})();
