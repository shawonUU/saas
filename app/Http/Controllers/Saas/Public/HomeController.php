<?php

namespace App\Http\Controllers\Saas\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(){
        return view('portals.landing', [
            'title' => 'SaaS Platform', 
            'description' => t('Manage your shops in one place.', 'এক জায়গায় আপনার শপ পরিচালনা করুন।'),
            'loginUrl' => route('saas.login'), 
            'registerUrl' => route('saas.register'),
            'dashboardUrl' => route('saas.dashboard'), 
            'guard' => 'web',
        ]);
    }
}
