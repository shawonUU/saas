@extends('public.business.public.ecommerce.account.shell')
@section('title',t('Returns','রিটার্ন'))
@section('account_heading',t('My returns','আমার রিটার্ন'))
@section('account_content')
<div class="ec-card"><h2>{{ t('Return history','রিটার্নের ইতিহাস') }}</h2>@include('public.business.public.ecommerce.account.return-list') @include('public.business.public.ecommerce.account.pagination',['page'=>$requests])</div>
@endsection
