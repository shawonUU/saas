<?php

namespace App\Http\Controllers\Public\Business\Admin\Settings\Business\GeneralSettings;

use App\Http\Controllers\Controller;
use App\Models\Public\Business\Admin\Settings\Business\GeneralSettings\BrandSetting;
use App\Services\Public\Business\Admin\Authorization\AccessControl\Access;
use App\Services\Public\Business\Admin\Settings\Business\GeneralSettings\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GeneralSettingsController extends Controller
{
    public function index(Request $request, Access $access, BrandingService $branding): View
    {
        $data = $access->check($request, 'settings', 'business', 'general-settings');
        $data['branding'] = $branding->find($data['business']);

        return view('public.business.admin.settings.business.general-settings.index', $data);
    }

    public function update(Request $request, Access $access): RedirectResponse
    {
        $data = $access->check($request, 'settings', 'business', 'general-settings', 'update');
        $business = $data['business'];
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:3000',
        ]);

        DB::transaction(function () use ($business, $validated, $request): void {
            $business->update(array_intersect_key($validated, array_flip(['name', 'phone', 'email', 'address'])));
            if ($request->exists('about')) {
                BrandSetting::updateOrCreate(['business_id' => $business->id], ['about' => $validated['about'] ?? null]);
            }
        });

        return back()->with('success', t('Business settings updated.', 'বিজনেস সেটিংস আপডেট হয়েছে।'));
    }

    // Existing bookmarks to Settings > Business > Branding should lead to the new screen.
    public function branding(Request $request, Access $access): RedirectResponse
    {
        $access->check($request, 'settings', 'business', 'branding');

        return redirect()->route('business.admin.settings.ecommerce.general-settings.index', [
            'business' => $request->route('business'),
        ]);
    }
}
