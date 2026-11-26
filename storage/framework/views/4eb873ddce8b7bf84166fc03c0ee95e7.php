<?php
    $posLayoutBusiness = $currentBusiness ?? $business ?? request()->attributes->get('business');
    $posLayoutBusiness = $posLayoutBusiness instanceof \App\Models\Business ? $posLayoutBusiness : null;
    $posLayoutBrandingService = app(\App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService::class);
    $posLayoutBranding = $posLayoutBrandingService->find($posLayoutBusiness);
    $posLayoutFavicon = $posLayoutBrandingService->imageUrl($posLayoutBranding?->favicon);
    $posLayoutPrimary = $posLayoutBranding?->primary_color ?? '#5145e5';
    $posLayoutStoreName = t($posLayoutBusiness?->name, $posLayoutBusiness?->name_bn) ?: t('Restaurant', 'রেস্টুরেন্ট');
?>
<!doctype html>
<html lang="<?php echo e(session('portal_lang', 'en')); ?>" data-theme="light">
<head>
    <meta charset="utf-8">
    <?php echo $__env->make('portals.partials.ui-theme-bootstrap', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e(t('Restaurant POS', 'রেস্টুরেন্ট পস')); ?> | <?php echo e($posLayoutStoreName); ?></title>
    <?php if($posLayoutFavicon): ?><link rel="icon" href="<?php echo e($posLayoutFavicon); ?>"><?php endif; ?>
    <style>
        :root{--primary:<?php echo e(preg_match('/^#[0-9a-fA-F]{6}$/', $posLayoutPrimary) ? $posLayoutPrimary : '#5145e5'); ?>;--primary-dark:#4035c7;--dark:#17223a;--text:#414861;--muted:#66758d;--border:#dfe5ef;--bg:#f6f8fc;--white:#fff}
        *,*::before,*::after{box-sizing:border-box}
        html,body{margin:0;min-height:100%;width:100%}
        body{font-family:'DM Sans','Noto Sans Bengali','Nirmala UI',system-ui,sans-serif;-webkit-font-smoothing:antialiased;line-height:1.5}
        h1,h2,h3,p{margin:0}
        a{color:inherit;text-decoration:none}
        button,input,select,textarea{font:inherit}
        button{touch-action:manipulation}
    </style>
    <?php echo $__env->make('portals.partials.theme-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('portals.partials.ui-foundation', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('public.business.admin.sales.restaurant-pos.styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="restaurant-pos-page">
    <?php echo $__env->yieldContent('content'); ?>
    <?php echo $__env->make('portals.partials.ui-controls', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\laragon\www\saas-backend\resources\views/public/business/admin/sales/restaurant-pos/layout.blade.php ENDPATH**/ ?>