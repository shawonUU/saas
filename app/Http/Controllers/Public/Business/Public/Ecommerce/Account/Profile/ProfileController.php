<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Profile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class ProfileController extends Controller {
 public function index(Request $r){return view('public.business.public.ecommerce.account.profile',['customer'=>$r->user('business_customer')]);}
 public function update(Request $r){ $data=$r->validate(['name'=>'required|string|max:255','name_bn'=>'nullable|string|max:255','phone'=>'nullable|string|max:40']);$r->user('business_customer')->update($data);return back()->with('success',t('Profile updated.','প্রোফাইল আপডেট হয়েছে।')); }
 public function password(Request $r){ $data=$r->validate(['current_password'=>'required|current_password:business_customer','password'=>'required|string|min:8|confirmed']);$r->user('business_customer')->update(['password'=>$data['password']]);$r->session()->regenerate();return back()->with('success',t('Password updated.','পাসওয়ার্ড আপডেট হয়েছে।')); }
}
