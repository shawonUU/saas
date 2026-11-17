<div class="ret-field"><label>{{ t('Items / Variants','পণ্য / ভ্যারিয়েন্ট') }}</label><div id="ret-items"></div><button type="button" class="ret-btn alt" id="ret-add-item">+ {{ t('Add Item','পণ্য যোগ করুন') }}</button></div>
<template id="ret-line-template"><div class="ret-cart-row {{ ($purchaseMode??false)?'purchase':'' }}"><select name="items[__N__][variant_id]" class="ret-input" required><option value="">{{ t('Choose product / variant','পণ্য / ভ্যারিয়েন্ট বাছাই') }}</option>
 @foreach($variants as $variant)<option value="{{ $variant->id }}" data-price="{{ $purchaseMode??false ? $variant->cost : $variant->price }}">{{ $variant->product?->name }} — {{ $variant->sku }} ({{ number_format($variant->stock,2) }})</option>@endforeach
 </select><input class="ret-input" type="number" name="items[__N__][quantity]" min="0.001" step="0.001" value="1" required aria-label="Quantity">
 @if($purchaseMode??false)<input class="ret-input" type="number" name="items[__N__][cost]" min="0" step=".01" placeholder="{{ t('Unit cost','ক্রয়মূল্য') }}" required>@else<span class="ret-help">{{ t('Price from catalog','মূল্য ক্যাটালগ অনুযায়ী') }}</span>@endif
 <button type="button" class="ret-btn alt ret-delete">×</button></div></template>
<script>
 document.addEventListener('DOMContentLoaded',()=>{const holder=document.getElementById('ret-items'),template=document.getElementById('ret-line-template');if(!holder||!template)return;let n=0;
 const add=()=>{const block=template.innerHTML.replaceAll('__N__',String(n++));holder.insertAdjacentHTML('beforeend',block);const row=holder.lastElementChild;row.querySelector('.ret-delete').onclick=()=>row.remove();const sel=row.querySelector('select');sel.onchange=()=>{const cost=row.querySelector('input[name$="[cost]"]');if(cost&&sel.selectedOptions[0])cost.value=sel.selectedOptions[0].dataset.price||0;};};
 document.getElementById('ret-add-item').onclick=add;add();});
</script>
