@extends('public.business.public.ecommerce.account.shell')
@section('title',t('My orders','আমার অর্ডার'))
@section('account_heading',t('My orders','আমার অর্ডার'))
@section('account_content')
<div class="ec-card"><h2>{{ t('Order history','অর্ডারের ইতিহাস') }}</h2><form class="ec-form" method="get"><label>{{ t('Order number','অর্ডার নম্বর') }}<input name="q" value="{{ request('q') }}" maxlength="80" placeholder="SO-123"></label><label>{{ t('Delivery status','ডেলিভারি অবস্থা') }}<select name="delivery"><option value="">{{ t('All','সব') }}</option>@foreach(['pending'=>['Pending','অপেক্ষমাণ'],'packed'=>['Packed','প্যাক করা'],'shipped'=>['Shipped','পাঠানো হয়েছে'],'delivered'=>['Delivered','ডেলিভারি হয়েছে']] as $key=>$label)<option value="{{ $key }}" @selected(request('delivery')===$key)>{{ t(...$label) }}</option>@endforeach</select></label><button class="ec-btn">{{ t('Search','খুঁজুন') }}</button></form>@include('public.business.public.ecommerce.account.order-list') @include('public.business.public.ecommerce.account.pagination',['page'=>$orders])</div>
@endsection
