<?php $__env->startSection('title',$item->name); ?>
<?php
$detailBrand=app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
$selectedVariant=$item->variants->first(fn($v)=>!$item->track_stock||(float)$v->stock>0)??$item->variants->first();
$detailPhoto=$detailBrand->imageUrl($selectedVariant?->image_path?:$item->image_url);
$detailPrice=(float)($selectedVariant?->price??$item->price);
$detailBefore=(float)($item->compare_at_price??0);
$detailOff=$detailBefore>$detailPrice&&$detailPrice>0?round((1-$detailPrice/$detailBefore)*100):0;
$gallery=collect([$item->image_url])->merge($item->variants->pluck('image_path'))->filter()->unique()->map(fn($path)=>$detailBrand->imageUrl($path))->values();
?>
<?php $__env->startSection('content'); ?>
<div class="sf-page-heading sf-page-heading-small"><a href="<?php echo e(route('business.customer.shop')); ?>" class="sf-back">‹</a><h1><?php echo e(t('Product Details','পণ্যের বিস্তারিত')); ?></h1></div>
<div class="sf-details-title"><?php echo e($item->name); ?></div>
<div class="sf-detail-cols">
 <div class="sf-gallery"><div class="sf-gallery-thumbs"><?php $__currentLoopData = $gallery->take(12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><button class="sf-gallery-thumb" data-gallery-src="<?php echo e($photo); ?>" type="button"><img src="<?php echo e($photo); ?>" alt="<?php echo e($item->name); ?>"></button><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div><div class="sf-main-art"><?php if($detailPhoto): ?><img id="sf-detail-image" src="<?php echo e($detailPhoto); ?>" alt="<?php echo e($item->name); ?>"><?php else: ?><div class="sf-placeholder">▧</div><?php endif; ?> <?php if($detailOff): ?><span class="sf-sale-badge sf-detail-sale" id="sf-pdp-discount">-<?php echo e($detailOff); ?>%</span><?php endif; ?></div></div>
 <section class="sf-pdp-order"><div class="sf-pdp-prices"><strong id="sf-detail-price">৳ <?php echo e(number_format($detailPrice,2)); ?></strong><?php if($detailOff): ?><del id="sf-before-price">৳<?php echo e(number_format($detailBefore,2)); ?></del><small id="sf-saving-badge">-<?php echo e($detailOff); ?>%</small><?php else: ?><del id="sf-before-price" hidden></del><small id="sf-saving-badge" hidden></small><?php endif; ?></div><p class="sf-detail-sku"><?php echo e(t('SKU','SKU')); ?>: <?php echo e($item->sku); ?></p><h2><?php echo e(t('Color, Size & Variants','রং, সাইজ ও ভ্যারিয়েন্ট')); ?></h2>
 <?php
 $colorGroups=$item->variants->groupBy(fn($v)=>$v->color_id??0);
 $hasColor=$item->variants->contains(fn($v)=>$v->color_id!==null);
 $hasSize=$item->variants->contains(fn($v)=>$v->size_id!==null);
 $initialColor=$selectedVariant?->color_id??0;
 ?>
 <?php if($hasColor): ?>
 <div class="pdp-color-label"><?php echo e(t('Color','রং')); ?>: <strong id="pdp-color-name"><?php echo e($selectedVariant?->color?->name??t('No color','রং ছাড়া')); ?></strong></div>
 <div class="pdp-colors" role="group" aria-label="<?php echo e(t('Color','রং')); ?>">
 <?php $__currentLoopData = $colorGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $colorId=>$variants): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
 <?php $colorVariant=$variants->first(fn($v)=>$v->image_path)??$variants->first(); $colorName=$colorVariant->color?->name??t('No color','রং ছাড়া'); ?>
 <button type="button" class="pdp-color <?php if($colorId==$initialColor): ?> active <?php endif; ?>" data-color="<?php echo e($colorId); ?>" data-label="<?php echo e($colorName); ?>" aria-pressed="<?php echo e($colorId==$initialColor?'true':'false'); ?>" title="<?php echo e($colorName); ?>"><?php if($colorVariant->image_path||$item->image_url): ?><img src="<?php echo e($detailBrand->imageUrl($colorVariant->image_path?:$item->image_url)); ?>" alt="<?php echo e($colorName); ?>"><?php else: ?><span><?php echo e($colorName); ?></span><?php endif; ?></button>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
 </div>
 <?php endif; ?>
 <div class="pdp-size-list">
 <?php $__currentLoopData = $colorGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $colorId=>$variants): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
 <?php $__currentLoopData = $variants->sortBy(fn($v)=>$v->size?->name??'',SORT_NATURAL); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
 <?php $available=!$item->track_stock||(float)$variant->stock>0; ?>
 <div class="pdp-size-row" data-color-row="<?php echo e($colorId); ?>" <?php if($colorId!=$initialColor): ?> hidden <?php endif; ?>>
 <div class="pdp-size-name"><?php echo e($variant->size?->name??($hasSize?t('No size','সাইজ ছাড়া'):t('Standard','সাধারণ'))); ?><?php if($variants->count()>1 && $variants->where('size_id',$variant->size_id)->count()>1): ?><small><?php echo e($variant->sku); ?></small><?php endif; ?></div>
 <div class="pdp-row-price"><strong>৳ <?php echo e(number_format((float)$variant->price,2)); ?></strong><?php if($detailBefore>$variant->price): ?><del>৳ <?php echo e(number_format($detailBefore,2)); ?></del><?php endif; ?></div>
 <div class="pdp-row-action"><button type="button" class="pdp-row-add" data-row-variant="<?php echo e($variant->id); ?>" <?php if(!$available): echo 'disabled'; endif; ?>>🛒 <?php echo e(t('Add','যোগ করুন')); ?></button><small><?php echo e(!$item->track_stock?t('Available','স্টকে আছে'):($available?t('Stock','স্টক').' '.number_format((float)$variant->stock,3,'.',''):t('Out of stock','স্টক নেই'))); ?></small></div>
 </div>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
 <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
 </div>
 <label class="sf-detail-select" style="display:none"><?php echo e(t('Selected Variant','নির্বাচিত ভ্যারিয়েন্ট')); ?><select id="sf-detail-variant" data-compare="<?php echo e($detailBefore); ?>"><?php $__currentLoopData = $item->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($variant->id); ?>" data-stock="<?php echo e($item->track_stock?$variant->stock:99999); ?>" data-price="<?php echo e($variant->price); ?>" data-image="<?php echo e($detailBrand->imageUrl($variant->image_path?:$item->image_url)); ?>" <?php if($selectedVariant?->id===$variant->id): echo 'selected'; endif; ?> <?php if($item->track_stock && $variant->stock<=0): echo 'disabled'; endif; ?>><?php echo e(collect([$variant->size?->name,$variant->color?->name,$variant->sku])->filter()->implode(' · ')); ?> · ৳<?php echo e(number_format($variant->price,2)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
 <div class="sf-pdp-stock" id="sf-detail-stock" style="display:none"><?php echo e(t('Choose a variant to see availability','স্টক দেখতে ভ্যারিয়েন্ট বেছে নিন')); ?></div><div class="sf-detail-buttons"><button class="sf-btn sf-pdp-add" id="sf-detail-add" style="display:none" data-detail-add data-product="<?php echo e($item->id); ?>" data-name="<?php echo e($item->name); ?>" type="button"><?php echo e(t('Add to Cart','কার্টে যোগ করুন')); ?></button><button class="sf-btn sf-pdp-buy" type="button" data-buy-now style="display:none"><?php echo e(t('Buy Now','এখনই কিনুন')); ?></button><button class="sf-pdp-wish" type="button" data-wish-toggle data-id="<?php echo e($item->id); ?>" data-name="<?php echo e($item->name); ?>" data-price="<?php echo e($detailPrice); ?>" data-url="<?php echo e(route('business.customer.product',['product'=>$item->id])); ?>" data-image="<?php echo e($detailPhoto); ?>" aria-label="Wishlist">♡</button></div>
 </section>
 <aside class="sf-pdp-recommend"><h3><?php echo e(t('Suggestions','আরও পণ্য')); ?></h3><?php $__currentLoopData = $related->take(12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recommendation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><a href="<?php echo e(route('business.customer.product',['product'=>$recommendation->id])); ?>"><span><?php $rPhoto=$detailBrand->imageUrl($recommendation->image_url?:$recommendation->variants->first()?->image_path); ?> <?php if($rPhoto): ?><img src="<?php echo e($rPhoto); ?>" alt=""><?php else: ?>  a <?php endif; ?></span><div><small><?php echo e($recommendation->name); ?></small><strong>৳<?php echo e(number_format((float)($recommendation->variants->first()?->price??$recommendation->price),2)); ?></strong></div></a><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></aside>
</div>
<section class="sf-details-tabs"><h3><?php echo e(t('Specifications','বিস্তারিত তথ্য')); ?></h3><div class="sf-spec-row"><span><?php echo e(t('Product','পণ্য')); ?></span><strong><?php echo e($item->name); ?></strong></div><div class="sf-spec-row"><span>SKU</span><strong><?php echo e($item->sku); ?></strong></div><div class="sf-spec-row"><span><?php echo e(t('Variants','ভ্যারিয়েন্ট')); ?></span><strong><?php echo e($item->variants->count()); ?></strong></div><?php $__currentLoopData = (json_decode($item->specifications??'[]',true)?:[]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $spec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(is_array($spec)&&!empty($spec['name'])&&!empty($spec['value'])): ?><div class="sf-spec-row"><span><?php echo e($spec['name']); ?></span><strong><?php echo e($spec['value']); ?></strong></div><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php if($item->description): ?><div class="sf-spec-row"><span><?php echo e(t('Description','বিবরণ')); ?></span><strong style="white-space:pre-wrap"><?php echo e($item->description); ?></strong></div><?php endif; ?></section>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.sf-detail-cols{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(0,1fr);gap:12px;align-items:start}.sf-pdp-recommend{grid-column:1/-1}.sf-gallery,.sf-pdp-order{min-width:0}.pdp-color-label{margin:12px 0 8px;font-size:14px}.pdp-colors{display:flex;flex-wrap:wrap;gap:9px;margin-bottom:14px}.pdp-color{position:relative;width:58px;height:58px;border:1px solid #d0d5db;border-radius:8px;background:var(--sf-card,#fff);padding:3px;cursor:pointer;overflow:hidden;color:#222}.pdp-color img{width:100%;height:100%;object-fit:contain}.pdp-color.active{border:2px solid #d6224e}.pdp-color.active:after{content:'✓';position:absolute;right:3px;bottom:3px;background:#d6224e;color:white;border-radius:50%;width:18px;height:18px;line-height:18px}.pdp-size-list{border:1px solid #80808040;border-radius:8px;overflow:auto;max-height:430px}.pdp-size-row{display:grid;grid-template-columns:minmax(55px,.7fr) minmax(85px,1fr) minmax(90px,1fr);align-items:center;gap:8px;padding:12px;border-bottom:1px solid #80808030}.pdp-size-row[hidden]{display:none!important}.pdp-size-row:nth-child(even){background:#80808008}.pdp-size-name{font-weight:600}.pdp-size-name small,.pdp-row-price del,.pdp-row-action small{display:block;font-size:11px;opacity:.75}.pdp-row-action{text-align:center}.pdp-row-add{border:0;border-radius:22px;background:#003b44;color:white;padding:7px 15px;font-weight:600;cursor:pointer}.pdp-row-add:disabled{opacity:.4;cursor:not-allowed}.pdp-row-price strong{font-size:14px}.pdp-row-action small{margin-top:5px}.sf-pdp-order h2{font-size:15px}@media(max-width:760px){.sf-detail-cols{grid-template-columns:minmax(0,1fr)}.pdp-size-list{max-height:none}}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 const select=document.getElementById('sf-detail-variant'), add=document.getElementById('sf-detail-add');
 if(!select||!add)return;
 const rows=[...document.querySelectorAll('[data-color-row]')], colors=[...document.querySelectorAll('.pdp-color[data-color]')];
 const choose=id=>{select.value=id;select.dispatchEvent(new Event('change',{bubbles:true}));};
 colors.forEach(btn=>btn.addEventListener('click',()=>{
 colors.forEach(c=>{c.classList.toggle('active',c===btn);c.setAttribute('aria-pressed',String(c===btn));});
 document.getElementById('pdp-color-name').textContent=btn.dataset.label;
 rows.forEach(row=>row.hidden=row.dataset.colorRow!==btn.dataset.color);
 const visible=rows.filter(row=>!row.hidden).map(row=>row.querySelector('[data-row-variant]'));
 const first=visible.find(b=>!b.disabled)||visible[0];if(first)choose(first.dataset.rowVariant);
 }));
 rows.forEach(row=>row.querySelector('[data-row-variant]').addEventListener('click',e=>{
 const btn=e.currentTarget;if(btn.disabled)return;choose(btn.dataset.rowVariant);if(!add.disabled)add.click();
 }));
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('public.business.public.ecommerce.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/public/ecommerce/catalog/show.blade.php ENDPATH**/ ?>