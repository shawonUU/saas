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
        $data = $access->check($request, 'settings', 'business', $request->route('child') === 'branding' ? 'branding' : 'general-settings');
        $data['branding'] = $branding->find($data['business']);

        return view('public.business.admin.settings.business.general-settings.index', $data);
    }

    public function update(Request $request, Access $access, BrandingService $branding): RedirectResponse
    {
        $data = $access->check($request, 'settings', 'business', 'general-settings', 'update');
        $business = $data['business'];
        $validated = $request->validate([
            'name' => 'required|string|max:255', 'phone' => 'nullable|string|max:50', 'email' => 'nullable|email|max:255', 'address' => 'nullable|string|max:255',
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'tagline' => 'nullable|string|max:255', 'about' => 'nullable|string|max:3000',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072', 'favicon' => 'nullable|file|mimes:ico,png,webp,jpg,jpeg|max:1024',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', 'secondary_banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $old = $branding->find($business);
        $oldLogo = $business->logo;
        $newPaths = [];
        try {
            foreach (['logo', 'favicon', 'banner', 'secondary_banner'] as $key) {
                if ($request->hasFile($key)) {
                    $newPaths[$key] = $branding->store($request->file($key), $business, $key);
                }
            }
            DB::transaction(function () use ($business, $validated, $newPaths): void {
                $businessValues = array_intersect_key($validated, array_flip(['name', 'phone', 'email', 'address']));
                if (isset($newPaths['logo'])) {
                    $businessValues['logo'] = $newPaths['logo'];
                }
                $business->update($businessValues);
                BrandSetting::updateOrCreate(['business_id' => $business->id], array_merge([
                    'primary_color' => $validated['primary_color'], 'tagline' => $validated['tagline'] ?? null, 'about' => $validated['about'] ?? null,
                ], array_intersect_key($newPaths, array_flip(['favicon', 'banner', 'secondary_banner']))));
            });
        } catch (\Throwable $exception) {
            foreach ($newPaths as $path) {
                $branding->discardOld($path, $business);
            }
            throw $exception;
        }
        foreach ($newPaths as $key => $path) {
            $branding->discardOld($key === 'logo' ? $oldLogo : $old?->{$key}, $business);
        }

        return back()->with('success', t('Business settings updated.', 'বিজনেস সেটিংস আপডেট হয়েছে।'));
    }
}
