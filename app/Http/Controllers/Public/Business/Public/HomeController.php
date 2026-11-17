<?php

namespace App\Http\Controllers\Public\Business\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $business = $request->attributes->get('current_business');
        // Retail and e-commerce stores show live purchasable inventory, not demo catalog.
        if (in_array($business->category?->slug, ['retail','grocery','ecommerce','online-store'], true)) {
            return redirect()->route('store.customer.shop');
        }
        return view('public.business.public.home', [
            'title' => $business->name, 
            'description' => t('Welcome to our store.', 'আমাদের স্টোরে স্বাগতম।'),
            'loginUrl' => route('store.customer.login', ['business' => $business->domain]),
            'registerUrl' => route('store.customer.register', ['business' => $business->domain]),
            'dashboardUrl' => route('store.customer.dashboard', ['business' => $business->domain]),
            'guard' => 'business_customer',
        ]);
    }
}
