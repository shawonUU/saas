<?php
namespace Modules\Business\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Business\App\Models\Business;

class ShopDomainController extends Controller
{
    public function availability(Request $request)
    {
        $slug = strtolower(trim((string) $request->query('slug')));
        $valid = (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
            && strlen($slug) >= 3 && strlen($slug) <= 50
            && !in_array($slug, ['www','admin','api','app','mail','support','localhost','saas'], true);
        return response()->json([
            'slug' => $slug,
            'available' => $valid && !Business::where('slug', $slug)->exists(),
            'valid' => $valid,
        ]);
    }

    public function storefront(string $slug)
    {
        $business = Business::query()->with('category:id,name,slug')->where('slug', $slug)
            ->where('status', 'Active')->firstOrFail();
        return response()->json([
            'business' => $business->only(['id','name','slug','phone','email','status']),
            'category' => $business->category,
            'categories' => [],
            'products' => [],
            'message' => 'Product catalog is not implemented in this project yet.',
        ]);
    }
}
