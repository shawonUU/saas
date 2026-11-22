<div class="sf-overlay" data-sidebar-overlay hidden></div>
<aside class="sf-sidebar" data-sidebar aria-label="{{ t('Categories','ক্যাটাগরি') }}" aria-hidden="true"><div class="sf-sidebar-head"><strong>{{ t('Categories','ক্যাটাগরি') }}</strong><button type="button" data-sidebar-close aria-label="{{ t('Close','বন্ধ') }}">×</button></div>
<a href="{{ route('business.customer.home') }}">{{ t('Home','হোম') }}</a><a href="{{ route('business.customer.shop') }}">{{ t('All Products','সব পণ্য') }}</a>
@foreach(($categories??collect()) as $category)<details><summary>{{ $category->name }}</summary><a href="{{ route('business.customer.shop',['category'=>$category->id]) }}">{{ t('View all','সব দেখুন') }}</a>@foreach(($category->children??collect()) as $kid)<a href="{{ route('business.customer.shop',['category'=>$category->id,'sub_category'=>$kid->id]) }}">{{ $kid->name }}</a>@endforeach</details>@endforeach
</aside>
