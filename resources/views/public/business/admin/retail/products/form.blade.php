@extends('public.business.admin.layout')
@section('content')
<div class="ret-page">
@include('public.business.admin.retail._styles')
@include('public.business.admin.sidebar')
<div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div><main class="workspace-content">
<div class="ret-title"><div><button type="button" class="ret-btn alt ret-mobile-nav" data-ws-sidebar-toggle>☰</button><h1>{{ $record ? t('Edit Product','পণ্য সম্পাদনা') : t('New Product','নতুন পণ্য') }}</h1><p>{{ t('Business workspace','বিজনেস ওয়ার্কস্পেস') }}</p></div><div class="ret-actions"><a class="ret-btn alt" href="{{ route('business.admin.dashboard') }}">{{ t('Dashboard','ড্যাশবোর্ড') }}</a></div></div>
@if(session('success'))<div class="ret-toast">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ret-error">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ $record ? route('business.admin.retail.products.update',['record'=>$record->id]) : route('business.admin.retail.products.store') }}" class="ret-card">@csrf @if($record)@method('PUT')@endif
<div class="ret-grid">
<div class="ret-field"><label>{{ t('Product Name','পণ্যের নাম') }} *</label><input class="ret-input" name="name" value="{{ old('name',$record?->name) }}" required></div>
<div class="ret-field"><label>SKU *</label><input class="ret-input" name="sku" value="{{ old('sku',$record?->sku) }}" required></div>
<div class="ret-field"><label>{{ t('Image URL','ছবির URL') }}</label><input class="ret-input" name="image_url" value="{{ old('image_url',$record?->image_url) }}" type="url"></div>
<div class="ret-field"><label>{{ t('Category','ক্যাটাগরি') }}</label><select name="category_id" class="ret-input"><option value="">—</option>@foreach($categories as $item)<option value="{{ $item->id }}" @selected(old('category_id',$record?->category_id)==$item->id)>{{ $item->name }}</option>@endforeach</select></div>
<div class="ret-field"><label>{{ t('Brand','ব্র্যান্ড') }}</label><select name="brand_id" class="ret-input"><option value="">—</option>@foreach($brands as $item)<option value="{{ $item->id }}" @selected(old('brand_id',$record?->brand_id)==$item->id)>{{ $item->name }}</option>@endforeach</select></div>
<div class="ret-field"><label>{{ t('Unit','ইউনিট') }}</label><select name="unit_id" class="ret-input"><option value="">—</option>@foreach($units as $item)<option value="{{ $item->id }}" @selected(old('unit_id',$record?->unit_id)==$item->id)>{{ $item->name }}</option>@endforeach</select></div>
<div class="ret-field"><label>{{ t('Base Sale Price','মূল বিক্রয়মূল্য') }}</label><input class="ret-input" name="price" type="number" min="0" step=".01" value="{{ old('price',$record?->price??0) }}" required></div>
<div class="ret-field"><label>{{ t('Base Cost','ক্রয়মূল্য') }}</label><input class="ret-input" name="cost" type="number" min="0" step=".01" value="{{ old('cost',$record?->cost??0) }}" required></div>
<div class="ret-field"><label>{{ t('Track Stock','স্টক ট্র্যাক করুন') }}</label><select name="track_stock" class="ret-input"><option value="1" @selected(old('track_stock',$record?->track_stock??1)==1)>Yes</option><option value="0" @selected(old('track_stock',$record?->track_stock??1)==0)>No</option></select></div>
<div class="ret-field"><label>{{ t('Status','অবস্থা') }}</label><select name="is_active" class="ret-input"><option value="1" @selected(old('is_active',$record?->is_active??1)==1)>Active</option><option value="0" @selected(old('is_active',$record?->is_active??1)==0)>Inactive</option></select></div>
<div class="ret-field" style="grid-column:1/-1"><label>{{ t('Description','বিবরণ') }}</label><textarea name="description" class="ret-input">{{ old('description',$record?->description) }}</textarea></div>
</div><hr class="ret-divider"><h3>{{ t('Product Variants','পণ্যের ভ্যারিয়েন্ট') }}</h3><p class="ret-help">{{ t('Size and color are optional. Each SKU has its own price, cost and stock. Existing stock can only be changed through stock movement.','সাইজ ও রং ঐচ্ছিক। প্রতিটি SKU-এর আলাদা মূল্য, ক্রয়মূল্য ও স্টক থাকবে। পুরোনো স্টক পরিবর্তন করতে স্টক মুভমেন্ট ব্যবহার করুন।') }}</p>
<div class="ret-table-wrap"><table class="ret-table"><thead><tr><th>SKU</th><th>{{ t('Size','সাইজ') }}</th><th>{{ t('Color','রং') }}</th><th>{{ t('Price','মূল্য') }}</th><th>{{ t('Cost','ক্রয়মূল্য') }}</th><th>{{ t('Opening Stock','প্রারম্ভিক স্টক') }}</th><th></th></tr></thead><tbody id="variant-rows"></tbody></table></div><button type="button" class="ret-btn alt" id="add-variant">+ {{ t('Add Variant','ভ্যারিয়েন্ট যোগ') }}</button>
<div style="margin-top:22px;display:flex;gap:10px"><button class="ret-btn" type="submit">{{ t('Save Product','পণ্য সংরক্ষণ') }}</button><a class="ret-btn alt" href="{{ route('business.admin.retail.products.index') }}">{{ t('Cancel','বাতিল') }}</a></div></form>
@php
 $sizeOptions=$sizes->map(fn($x)=>['id'=>$x->id,'name'=>$x->name])->values()->all();
 $colorOptions=$colors->map(fn($x)=>['id'=>$x->id,'name'=>$x->name])->values()->all();
 $existingOptions=$record?->variants?->map(fn($v)=>['id'=>$v->id,'sku'=>$v->sku,'size_id'=>$v->size_id,'color_id'=>$v->color_id,'price'=>$v->price,'cost'=>$v->cost])->values()->all()??[];
@endphp
<script>
document.addEventListener('DOMContentLoaded',()=>{
const sizes=@json($sizeOptions),colors=@json($colorOptions);
const existing=@json($existingOptions);
const holder=document.getElementById('variant-rows');let n=0;const esc=x=>String(x??'').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('\"','&quot;').replaceAll("'",'&#39;');
function opt(rows,val){return '<option value="">—</option>'+rows.map(x=>`<option value="${x.id}" ${String(x.id)===String(val)?'selected':''}>${x.name.replaceAll('&','&amp;').replaceAll('<','&lt;')}</option>`).join('');}
function add(v={}){const i=n++,tr=document.createElement('tr');tr.innerHTML=`<td><input type="hidden" name="variants[${i}][id]" value="${v.id||''}"><input class="ret-input" required name="variants[${i}][sku]" value="${esc(v.sku)}"></td><td><select class="ret-input" name="variants[${i}][size_id]">${opt(sizes,v.size_id)}</select></td><td><select class="ret-input" name="variants[${i}][color_id]">${opt(colors,v.color_id)}</select></td><td><input class="ret-input" required type="number" step=".01" min="0" name="variants[${i}][price]" value="${v.price??document.querySelector('input[name=price]').value}"></td><td><input class="ret-input" required type="number" step=".01" min="0" name="variants[${i}][cost]" value="${v.cost??document.querySelector('input[name=cost]').value}"></td><td>${v.id?'—':`<input class="ret-input" type="number" min="0" step=".001" name="variants[${i}][opening_stock]" value="0">`}</td><td>${v.id?'':`<button type="button" class="ret-btn alt">×</button>`}</td>`;holder.append(tr);tr.querySelector('button')?.addEventListener('click',()=>tr.remove());}
existing.length?existing.forEach(add):add({});document.getElementById('add-variant').onclick=()=>add({});
});
</script>
</main></div>
@endsection
