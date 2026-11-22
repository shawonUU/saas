<?php $__env->startSection('title',t('Order received','অর্ডার গৃহীত')); ?>
<?php $__env->startSection('content'); ?>
<section style="max-width:580px;margin:75px auto;background:var(--sf-card);border:1px solid var(--sf-line);padding:48px 30px;border-radius:20px;text-align:center"><div style="color:#1c9b61;font-size:50px">✓</div><h1><?php echo e(t('Order received!','অর্ডার গ্রহণ করা হয়েছে!')); ?></h1><p><?php echo e(t('Order number','অর্ডার নম্বর')); ?>: <b><?php echo e($orderNo); ?></b></p><p style="color:var(--sf-muted);line-height:1.7"><?php echo e(t('The store will contact you about delivery and payment.','ডেলিভারি ও পেমেন্টের বিষয়ে দোকান থেকে যোগাযোগ করা হবে।')); ?></p><a class="sf-btn" style="margin-top:15px" href="<?php echo e(route('business.customer.shop')); ?>"><?php echo e(t('Continue Shopping','আরও কেনাকাটা করুন')); ?></a></section>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
try{
 const bid='<?php echo e($business->id); ?>',key='nexabiz:checkout:pending:'+bid,pending=JSON.parse(sessionStorage.getItem(key)||'null');
 if(Array.isArray(pending)&&pending.length){const cartKey='nexabiz:cart:'+bid,cart=JSON.parse(localStorage.getItem(cartKey)||'[]');
  const updated=cart.map(item=>{const line=pending.find(p=>String(p.id)===String(item.id));return line?{...item,qty:Math.max(0,Number(item.qty)-Number(line.qty))}:item}).filter(item=>item.qty>0);
  localStorage.setItem(cartKey,JSON.stringify(updated));sessionStorage.removeItem(key);
 }
}catch(e){}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('public.business.public.ecommerce.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/public/ecommerce/checkout/success.blade.php ENDPATH**/ ?>