<div class="sf-overlay" data-sidebar-overlay hidden></div>
<aside class="sf-sidebar" data-sidebar aria-label="{{ t('Categories','ক্যাটাগরি') }}" aria-hidden="true"><div class="sf-sidebar-head"><strong>{{ t('Categories','ক্যাটাগরি') }}</strong><button type="button" data-sidebar-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
<a href="{{ route('business.customer.home') }}">{{ t('Home','হোম') }}</a><a href="{{ route('business.customer.shop') }}">{{ t('All Products','সব পণ্য') }}</a>
<a href="{{ auth('business_customer')->check()?route('business.customer.dashboard'):route('business.customer.login') }}">{{ t('My account','আমার অ্যাকাউন্ট') }}</a>
@foreach(($categories??collect()) as $category)<details><summary>{{ t($category->name,$category->name_bn ?? $category->name) }}</summary><a href="{{ route('business.customer.shop',['category'=>$category->id]) }}">{{ t('View all','সব দেখুন') }}</a>@foreach(($category->children??collect()) as $kid)<a href="{{ route('business.customer.shop',['category'=>$category->id,'sub_category'=>$kid->id]) }}">{{ t($kid->name,$kid->name_bn ?? $kid->name) }}</a>@endforeach</details>@endforeach
</aside>
