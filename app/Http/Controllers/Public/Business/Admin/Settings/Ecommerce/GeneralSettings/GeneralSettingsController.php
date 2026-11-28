<?php

namespace App\Http\Controllers\Public\Business\Admin\Settings\Ecommerce\GeneralSettings;

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
        $data = $access->check($request, 'settings', 'ecommerce', 'general-settings');
        $data['branding'] = $branding->find($data['business']);

        return view('public.business.admin.settings.ecommerce.general-settings.index', $data);
    }

    public function update(Request $request, Access $access, BrandingService $branding): RedirectResponse
    {
        $data = $access->check($request, 'settings', 'ecommerce', 'general-settings', 'update');
        $business = $data['business'];
        $validated = $request->validate([
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'tagline' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'favicon' => 'nullable|file|mimes:ico,png,webp,jpg,jpeg|max:1024',
            'banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'secondary_banner' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);
        $previous = $branding->find($business);
        $previousLogo = $business->logo;
        $newPaths = [];
        try {
            foreach (['logo', 'favicon', 'banner', 'secondary_banner'] as $key) {
                if ($request->hasFile($key)) {
                    $newPaths[$key] = $branding->store($request->file($key), $business, $key);
                }
            }
            DB::transaction(function () use ($business, $validated, $newPaths): void {
                if (isset($newPaths['logo'])) {
                    $business->update(['logo' => $newPaths['logo']]);
                }
                $values = [
                    'primary_color' => $validated['primary_color'],
                    'tagline' => $validated['tagline'] ?? null,
                ];
                foreach (['favicon', 'banner', 'secondary_banner'] as $key) {
                    if (isset($newPaths[$key])) {
                        $values[$key] = $newPaths[$key];
                    }
                }
                // 'about' remains owned by Business General Settings.
                BrandSetting::updateOrCreate(['business_id' => $business->id], $values);
            });
        } catch (\Throwable $exception) {
            foreach ($newPaths as $path) {
                $branding->discardOld($path, $business);
            }
            throw $exception;
        }
        foreach ($newPaths as $key => $path) {
            $branding->discardOld($key === 'logo' ? $previousLogo : $previous?->{$key}, $business);
        }

        return back()->with('success', t('E-commerce settings updated.', 'ই-কমার্স সেটিংস আপডেট হয়েছে।'));
    }
}
