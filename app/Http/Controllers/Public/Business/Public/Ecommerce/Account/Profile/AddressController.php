<?php
namespace App\Http\Controllers\Public\Business\Public\Ecommerce\Account\Profile;
use App\Http\Controllers\Controller;
use App\Services\CustomerAddressService;
use Illuminate\Http\Request;
class AddressController extends Controller {
 public function index(Request $r,CustomerAddressService $s){return view('public.business.public.ecommerce.account.addresses',['customer'=>$r->user('business_customer'),'addresses'=>$s->query($r)->latest('id')->paginate(10)]);}
 public function store(Request $r,CustomerAddressService $s){$s->create($r);return back()->with('success',t('Address saved.','ঠিকানা সংরক্ষিত হয়েছে।'));}
 public function update(Request $r,string $business,int $address,CustomerAddressService $s){$s->query($r)->findOrFail($address)->update($s->data($r));return back()->with('success',t('Address updated.','ঠিকানা আপডেট হয়েছে।'));}
 public function destroy(Request $r,string $business,int $address,CustomerAddressService $s){$s->query($r)->findOrFail($address)->delete();return back()->with('success',t('Address removed.','ঠিকানা সরানো হয়েছে।'));}
}
