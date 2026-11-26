@extends('public.business.admin.layout')
@push('styles')
@include('public.business.admin.configurations._workspace_styles')
@include('public.business.admin.sales.settings.pos-settings._styles')
@endpush
@section('content')
<div class="workspace-layout pos-settings-page">
    @include('public.business.admin.sidebar')
    <div class="ws-sidebar-overlay" data-ws-sidebar-overlay></div>
    <main class="workspace-content">
        <header class="pos-settings-header">
            <small>{{ t('Sales / Settings','বিক্রয় / সেটিংস') }}</small>
            <h1>{{ t('POS Settings','পস সেটিংস') }}</h1>
        </header>
        @if(session('success'))
            <div class="pos-settings-message pos-settings-success" role="status">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="pos-settings-message pos-settings-error" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('business.admin.sales.settings.pos-settings.update', ['business' => $business->domain]) }}" class="pos-settings-card">
            @csrf
            @method('PUT')
            <div class="pos-settings-heading">
                <h2>{{ t('Restaurant · Quick Sale','রেস্টুরেন্ট · দ্রুত বিক্রয়') }}</h2>
                <p>{{ t('Changes apply to new orders only.','পরিবর্তন শুধু নতুন অর্ডারে প্রযোজ্য হবে।') }}</p>
            </div>
            <fieldset class="pos-settings-fields" @disabled(!$permissions['update'])>
                <legend class="pos-settings-sr-only">{{ t('Restaurant workflow','রেস্টুরেন্টের কাজের নিয়ম') }}</legend>
                <label class="pos-settings-field" for="pos-default-service">
                    <span>{{ t('Start new orders as','নতুন অর্ডারের ডিফল্ট সেবা') }}</span>
                    <select id="pos-default-service" name="restaurant_pos[default_service]" required>
                        <option value="takeaway" @selected(old('restaurant_pos.default_service', $restaurantPosSettings['default_service']) === 'takeaway')>{{ t('Counter / Takeaway','কাউন্টার / টেকঅ্যাওয়ে') }}</option>
                        <option value="dine_in" @selected(old('restaurant_pos.default_service', $restaurantPosSettings['default_service']) === 'dine_in')>{{ t('Dine-in','ডাইন-ইন') }}</option>
                        <option value="delivery" @selected(old('restaurant_pos.default_service', $restaurantPosSettings['default_service']) === 'delivery')>{{ t('Delivery','ডেলিভারি') }}</option>
                    </select>
                    <small>{{ t('Dine-in starts with table selection.','ডাইন-ইন অর্ডার শুরু হবে টেবিল নির্বাচন দিয়ে।') }}</small>
                </label>
                <label class="pos-settings-field" for="pos-kitchen-mode">
                    <span>{{ t('Kitchen tracking','কিচেন ট্র্যাকিং') }}</span>
                    <select id="pos-kitchen-mode" name="restaurant_pos[kitchen_mode]" required>
                        <option value="off" @selected(old('restaurant_pos.kitchen_mode', $restaurantPosSettings['kitchen_mode']) === 'off')>{{ t('Off','বন্ধ') }}</option>
                        <option value="optional" @selected(old('restaurant_pos.kitchen_mode', $restaurantPosSettings['kitchen_mode']) === 'optional')>{{ t('Optional','ঐচ্ছিক') }}</option>
                        <option value="required" @selected(old('restaurant_pos.kitchen_mode', $restaurantPosSettings['kitchen_mode']) === 'required')>{{ t('Required','বাধ্যতামূলক') }}</option>
                    </select>
                    <small>{{ t('Optional keeps billing and table closing independent of the kitchen.','ঐচ্ছিক থাকলে বিল বা টেবিল বন্ধ করতে কিচেনের ধাপ শেষ হওয়া লাগবে না।') }}</small>
                </label>
                <label class="pos-settings-field" for="pos-auto-send">
                    <span>{{ t('Send to kitchen when billing','বিল নিশ্চিত করলে কিচেনে পাঠান') }}</span>
                    <select id="pos-auto-send" name="restaurant_pos[auto_send_to_kitchen]" required>
                        <option value="0" @selected(! old('restaurant_pos.auto_send_to_kitchen', $restaurantPosSettings['auto_send_to_kitchen']))>{{ t('No · send manually','না · প্রয়োজন হলে পাঠাব') }}</option>
                        <option value="1" @selected(old('restaurant_pos.auto_send_to_kitchen', $restaurantPosSettings['auto_send_to_kitchen']))>{{ t('Yes · send automatically','হ্যাঁ · স্বয়ংক্রিয়ভাবে পাঠান') }}</option>
                    </select>
                    <small>{{ t('Hold only saves. Kitchen Off always disables automatic sending.','হোল্ড করলে শুধু সংরক্ষণ হয়। কিচেন বন্ধ থাকলে স্বয়ংক্রিয়ভাবে পাঠানোও বন্ধ থাকবে।') }}</small>
                </label>
            </fieldset>
            <p class="pos-settings-note">{{ t('Close dine-in tables when guests leave. Bill and KOT printing are always available.','অতিথি চলে গেলে ডাইন-ইন টেবিল বন্ধ করুন। বিল ও KOT প্রিন্ট সবসময় করা যাবে।') }}</p>
            @if($permissions['update'])
                <div class="pos-settings-actions"><button type="submit" class="pos-settings-save">{{ t('Save Settings','সেটিংস সংরক্ষণ') }}</button></div>
            @endif
        </form>
    </main>
</div>
@endsection
