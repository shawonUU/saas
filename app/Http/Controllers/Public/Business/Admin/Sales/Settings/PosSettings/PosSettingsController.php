<?php

namespace App\Http\Controllers\Public\Business\Admin\Sales\Settings\PosSettings;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Sales\Settings\PosSettings\PosSettingsService;
use App\Services\Public\Business\Admin\Settings\NavigationManagement\BusinessNavigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosSettingsController extends Controller
{
    public function index(Request $request, Access $access, PosSettingsService $settings, BusinessNavigation $navigation): View
    {
        $data = $access->check($request, 'sales', 'settings', 'pos-settings');
        if (! $this->restaurantPosEnabled($data['business'], $navigation)) {
            $data['module'] = $data['modules']->firstWhere('slug', 'sales');
            $data['menu'] = $data['module']->menus->firstWhere('slug', 'settings');
            $data['child'] = $data['menu']->children->firstWhere('slug', 'pos-settings');
            $data['child']->loadMissing('menu.module');

            return view('public.business.admin.feature', $data);
        }
        $data['permissions'] = $access->permissions($data, 'sales', 'settings', 'pos-settings');
        $data['restaurantPosSettings'] = $settings->forBusiness($data['business']);

        return view('public.business.admin.sales.settings.pos-settings.index', $data);
    }

    public function update(Request $request, Access $access, PosSettingsService $settings, BusinessNavigation $navigation): RedirectResponse
    {
        $data = $access->check($request, 'sales', 'settings', 'pos-settings', 'update');
        abort_unless($this->restaurantPosEnabled($data['business'], $navigation), 403);
        $validated = $request->validate([
            'restaurant_pos' => 'required|array:kitchen_mode,auto_send_to_kitchen,default_service|required_array_keys:kitchen_mode,auto_send_to_kitchen,default_service',
            'restaurant_pos.kitchen_mode' => 'required|string|in:off,optional,required',
            'restaurant_pos.auto_send_to_kitchen' => 'required|boolean',
            'restaurant_pos.default_service' => 'required|string|in:takeaway,dine_in,delivery',
        ], [
            'restaurant_pos.required' => t('Choose the kitchen mode, automatic kitchen sending and default service.', 'কিচেনের ধরন, স্বয়ংক্রিয়ভাবে কিচেনে পাঠানো এবং ডিফল্ট সেবা নির্বাচন করুন।'),
            'restaurant_pos.array' => t('Choose only the available restaurant POS settings.', 'রেস্টুরেন্ট পসের নির্ধারিত সেটিংসগুলোই নির্বাচন করুন।'),
            'restaurant_pos.required_array_keys' => t('Choose the kitchen mode, automatic kitchen sending and default service.', 'কিচেনের ধরন, স্বয়ংক্রিয়ভাবে কিচেনে পাঠানো এবং ডিফল্ট সেবা নির্বাচন করুন।'),
            'restaurant_pos.kitchen_mode.in' => t('Choose Off, Optional or Required for kitchen tracking.', 'কিচেন ট্র্যাকিংয়ের জন্য বন্ধ, ঐচ্ছিক বা বাধ্যতামূলক নির্বাচন করুন।'),
            'restaurant_pos.auto_send_to_kitchen.boolean' => t('Choose whether billing sends items to the kitchen.', 'বিল নিশ্চিত করলে খাবার কিচেনে যাবে কি না নির্বাচন করুন।'),
            'restaurant_pos.default_service.in' => t('Choose Counter / Takeaway, Dine-in or Delivery as the default service.', 'ডিফল্ট সেবা হিসেবে কাউন্টার / টেকঅ্যাওয়ে, ডাইন-ইন বা ডেলিভারি নির্বাচন করুন।'),
        ]);
        $settings->save($data['business'], $validated['restaurant_pos']);

        return redirect()->route('business.admin.sales.settings.pos-settings.index', ['business' => $data['business']->domain])
            ->with('success', t('POS settings updated.', 'পস সেটিংস আপডেট হয়েছে।'));
    }

    private function restaurantPosEnabled(Business $business, BusinessNavigation $navigation): bool
    {
        return $navigation->forBusiness($business)->firstWhere('slug', 'sales')?->menus->contains('slug', 'restaurant-pos') ?? false;
    }
}
