<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('public.dashboard', [
            'user' => Auth::guard('web')->user(),
            'businesss' => Business::where('owner_id', Auth::guard('web')->id())->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('public.business.create', [
            'user' => auth()->user(),
        ]);
    }
}
