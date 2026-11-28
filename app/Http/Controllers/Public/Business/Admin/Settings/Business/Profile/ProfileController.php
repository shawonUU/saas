<?php
namespace App\Http\Controllers\Public\Business\Admin\Settings\Business\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function password(Request $request): RedirectResponse
    {
        $business = $request->attributes->get('current_business');
        $user = $request->user('business_admin');
        abort_unless($business && $user && (int) $user->business_id === (int) $business->id, 403);

        $data = $request->validateWithBag('profilePassword', [
            'current_password' => ['required', 'current_password:business_admin'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->update(['password' => $data['password']]);
        // auth.session compares this hash on every request. Keep the current login valid.
        $request->session()->put('password_hash_business_admin', $user->getAuthPassword());
        $request->session()->regenerate();

        return back()->with('success', t('Password changed successfully.', 'পাসওয়ার্ড সফলভাবে পরিবর্তিত হয়েছে।'));
    }
}
