<?php
namespace App\Http\Controllers\Saas\OwnerPortal\ShopRegistration;
use App\Http\Controllers\Controller;
use App\Models\BusinessUser;
use App\Models\Shop;
use App\Models\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class ShopRegistrationController extends Controller
{
    public function create()
    {
        return view('portals.create-shop', ['categories' => ShopCategory::where('approval_status', 'Approved')->orderBy('name')->get()]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z](?:[a-z0-9-]{1,38}[a-z0-9])$/', Rule::notIn(config('saas.reserved_subdomains', [])), 'unique:shops,code'],
            'category_id' => ['required', Rule::exists('shop_categories', 'id')->where('approval_status', 'Approved')],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        $owner = Auth::guard('web')->user();
        $shop = DB::transaction(function () use ($data, $owner) {
            $shop = Shop::create([
                'owner_id' => $owner->id, 'category_id' => $data['category_id'],
                'name' => trim($data['name']), 'code' => $data['code'], 'approval_status' => 'Approved',
            ]);
            BusinessUser::create([
                'shop_id' => $shop->id, 'user_id' => $owner->id, 'role' => 'owner',
                'name' => $owner->name, 'email' => $owner->email,
                'code' => 'BU'.Str::upper(Str::random(12)),
                'password' => filled($data['admin_password'] ?? null) ? $data['admin_password'] : $owner->password,
                'approval_status' => 'Approved',
            ]);
            return $shop;
        });
        return redirect()->route('saas.dashboard')->with('shop_created', $shop->code)->with('success', 'Your shop is ready. Open its storefront or admin portal below.');
    }
}
