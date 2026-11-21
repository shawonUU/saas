<?php
namespace App\Services;
use App\Models\Business;
use App\Services\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class Access {
 public function check(Request $request,string $module,string $menu,?string $child=null,string $action='view'):array {
  $business=$request->attributes->get('current_business');$user=Auth::guard('business_admin')->user();
  abort_unless($business instanceof Business && $user && (int)$business->id===(int)$user->business_id,403);
  $modules=app(BusinessNavigation::class)->forBusiness($business,$user);
  $m=$modules->firstWhere('slug',$module);$entry=$m?->menus->firstWhere('slug',$menu);
  abort_unless($entry && ($child===null ? $entry->children->isEmpty() : $entry->children->contains('slug',$child)),403);
  if($action!=='view' && !$user->isOwnerOf($business)) {
   $query=$user->accesses()->where('module_id',$m->id)->where('menu_id',$entry->id)
     ->where('is_active',true)->where('approval_status','Approved')->where('can_view',true)->where('can_'.$action,true);
   if($child===null)$query->whereNull('child_menu_id');
   else $query->where('child_menu_id',$entry->children->firstWhere('slug',$child)->id);
   abort_unless($query->exists(),403);
  }
  return ['business'=>$business,'user'=>$user,'modules'=>$modules,'businessKey'=>$business->domain];
 }
}
