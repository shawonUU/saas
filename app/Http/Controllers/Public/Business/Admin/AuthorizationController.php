<?php
namespace App\Http\Controllers\Public\Business\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    BusinessUser,
    BusinessUserAccess,
    BusinessPermission,
    BusinessUserPermission,
    Module,
    Menu,
    ChildMenu,
    BusinessCategoryModule,
    BusinessCategoryMenu,
    BusinessCategoryChildMenu,
    BusinessModule,
    BusinessMenu,
    BusinessChildMenu
};
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AuthorizationController extends Controller
{
    private const FIELDS=['can_view','can_create','can_update','can_delete'];

    private function roleName(BusinessUser $user): string
    {
        return strtolower(trim((string)($user->role?->name ?? '')));
    }

    private function context(Request $request): array
    {
        $business=$request->attributes->get('current_business');
        abort_unless($business,404,'Business not found.');

        $authUser=$request->user();
        abort_unless($authUser,401,'Authentication required.');

        $actor=BusinessUser::query()
            ->with('role')
            ->where('business_id',$business->id)
            ->findOrFail($authUser->getAuthIdentifier());

        $role=$this->roleName($actor);

        abort_unless(in_array($role,['owner','admin'],true),403,'Only Owner or Admin can manage access.');

        return [$business,$actor,$role==='owner'];
    }

    private function editable(object $business,int $userId,bool $isOwner,bool $lock=false): BusinessUser
    {
        $query=BusinessUser::query()
            ->with('role')
            ->where('business_id',$business->id)
            ->whereKey($userId);

        $user=$lock ? $query->lockForUpdate()->firstOrFail() : $query->firstOrFail();
        $role=$this->roleName($user);

        abort_if($role==='owner'||(!$isOwner&&$role==='admin'),403,'You cannot modify this user.');

        return $user;
    }

    private function tree(object $business): Collection
    {
        $businessId=(int)$business->id;
        $categoryId=(int)$business->category_id;

        $moduleIds=BusinessCategoryModule::query()
            ->where('business_category_id',$categoryId)
            ->pluck('module_id');

        $disabledModules=BusinessModule::query()
            ->where('business_id',$businessId)
            ->where('is_enabled',false)
            ->pluck('module_id');

        $modules=Module::query()
            ->whereIn('id',$moduleIds)
            ->whereNotIn('id',$disabledModules)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $menuIds=BusinessCategoryMenu::query()
            ->where('business_category_id',$categoryId)
            ->pluck('menu_id');

        $disabledMenus=BusinessMenu::query()
            ->where('business_id',$businessId)
            ->where('is_enabled',false)
            ->pluck('menu_id');

        $menus=Menu::query()
            ->whereIn('module_id',$modules->pluck('id'))
            ->whereIn('id',$menuIds)
            ->whereNotIn('id',$disabledMenus)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $childIds=BusinessCategoryChildMenu::query()
            ->where('business_category_id',$categoryId)
            ->pluck('child_menu_id');

        $disabledChildren=BusinessChildMenu::query()
            ->where('business_id',$businessId)
            ->where('is_enabled',false)
            ->pluck('child_menu_id');

        $children=ChildMenu::query()
            ->whereIn('menu_id',$menus->pluck('id'))
            ->whereIn('id',$childIds)
            ->whereNotIn('id',$disabledChildren)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('menu_id');

        $menuGroups=$menus->groupBy('module_id');

        return $modules->map(function($module) use($menuGroups,$children){
            $module->setRelation('menus',
                ($menuGroups->get($module->id)??collect())
                    ->map(function($menu) use($children){
                        $menu->setRelation('children',$children->get($menu->id)??collect());
                        return $menu;
                    })->values()
            );
            return $module;
        });
    }

    private function definitions(object $business,Collection $modules): Collection
    {
        $moduleIds=$modules->pluck('id');
        $menuIds=$modules->flatMap(fn($module)=>$module->menus)->pluck('id');
        $childIds=$modules->flatMap(fn($module)=>$module->menus->flatMap(fn($menu)=>$menu->children))->pluck('id');

        return BusinessPermission::query()
            ->where(function($q) use($business){
                $q->whereNull('business_id')->orWhere('business_id',$business->id);
            })
            ->where(function($q) use($moduleIds,$menuIds,$childIds){
                $q->whereIn('module_id',$moduleIds)
                    ->orWhereIn('menu_id',$menuIds)
                    ->orWhereIn('child_menu_id',$childIds);
            })
            ->orderBy('id')
            ->get();
    }

    private function extras(Collection $definitions,array $granted,int $moduleId,?int $menuId=null,?int $childId=null): array
    {
        return $definitions->filter(function($item) use($moduleId,$menuId,$childId){
            if($item->module_id!==null&&(int)$item->module_id!==$moduleId)return false;

            if($childId!==null){
                return (int)$item->child_menu_id===$childId
                    &&($item->menu_id===null||(int)$item->menu_id===$menuId);
            }

            if($menuId!==null){
                return $item->child_menu_id===null&&(int)$item->menu_id===$menuId;
            }

            return $item->child_menu_id===null
                &&$item->menu_id===null
                &&(int)$item->module_id===$moduleId;
        })->map(fn($item)=>[
            'id'=>(int)$item->id,
            'name'=>$item->name,
            'key'=>$item->key,
            'description'=>$item->description,
            'allowed'=>in_array((int)$item->id,$granted,true)
        ])->values()->all();
    }

    private function allowedExtras(Collection $modules,Collection $definitions): array
    {
        $ids=[];

        foreach($modules as $module){
            foreach($this->extras($definitions,[],(int)$module->id) as $extra){
                $ids[]=$extra['id'];
            }

            foreach($module->menus as $menu){
                foreach($this->extras($definitions,[],(int)$module->id,(int)$menu->id) as $extra){
                    $ids[]=$extra['id'];
                }

                foreach($menu->children as $child){
                    foreach($this->extras($definitions,[],(int)$module->id,(int)$menu->id,(int)$child->id) as $extra){
                        $ids[]=$extra['id'];
                    }
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private function flags(?BusinessUserAccess $row): array
    {
        $result=[];

        foreach(self::FIELDS as $field){
            $result[$field]=(bool)($row?->$field??false);
        }

        return $result;
    }

    private function accessKey(int $moduleId,int $menuId,?int $childId): string
    {
        return $moduleId.':'.$menuId.':'.($childId??0);
    }

    public function index(Request $request)
    {
        [$business,$user,$isOwner]=$this->context($request);

        $members=BusinessUser::query()
            ->with('role')
            ->where('business_id',$business->id)
            ->orderBy('name')
            ->get()
            ->each(function($member) use($isOwner){
                $role=$this->roleName($member);
                $member->can_manage=$role!=='owner'&&($isOwner||$role!=='admin');
            });

        $modules=$this->tree($business);

        return view('public.business.admin.authorization.index',compact('business','user','members','modules'));
    }

    public function show(Request $request, $memberId)
    {
        [$business,,$isOwner]=$this->context($request);
        $target=$this->editable($business,(int)$memberId,$isOwner);
        $modules=$this->tree($business);
        $definitions=$this->definitions($business,$modules);

        $access=BusinessUserAccess::query()
            ->where('business_user_id',$target->id)
            ->get()
            ->keyBy(fn($row)=>$this->accessKey(
                (int)$row->module_id,
                (int)$row->menu_id,
                $row->child_menu_id===null?null:(int)$row->child_menu_id
            ));

        $granted=BusinessUserPermission::query()
            ->where('business_id',$business->id)
            ->where('user_id',$target->id)
            ->pluck('permission_id')
            ->map(fn($id)=>(int)$id)
            ->all();

        $tree=$modules->map(function($module) use($access,$definitions,$granted){
            return [
                'id'=>(int)$module->id,
                'code'=>$module->slug,
                'name'=>$module->name,
                'name_bn'=>$module->name_bn??null,
                'slug'=>$module->slug,
                'extra_permissions'=>$this->extras($definitions,$granted,(int)$module->id),
                'menus'=>$module->menus->map(function($menu) use($module,$access,$definitions,$granted){
                    $moduleId=(int)$module->id;
                    $menuId=(int)$menu->id;

                    return [
                        'id'=>$menuId,
                        'code'=>$menu->slug,
                        'name'=>$menu->name,
                        'name_bn'=>$menu->name_bn??null,
                        'slug'=>$menu->slug,
                        'permission'=>$menu->children->isEmpty()
                            ?$this->flags($access->get($this->accessKey($moduleId,$menuId,null)))
                            :null,
                        'extra_permissions'=>$this->extras($definitions,$granted,$moduleId,$menuId),
                        'child_menus'=>$menu->children->map(fn($child)=>[
                            'id'=>(int)$child->id,
                            'code'=>$child->slug,
                            'name'=>$child->name,
                            'name_bn'=>$child->name_bn??null,
                            'slug'=>$child->slug,
                            'permission'=>$this->flags($access->get($this->accessKey($moduleId,$menuId,(int)$child->id))),
                            'extra_permissions'=>$this->extras($definitions,$granted,$moduleId,$menuId,(int)$child->id)
                        ])->values()->all()
                    ];
                })->values()->all()
            ];
        })->values()->all();

        return response()->json(['data'=>['modules'=>$tree]]);
    }

    public function store(Request $request)
    {
        [$business,$actor,$isOwner]=$this->context($request);

        $data=$request->validate([
            'member_id'=>['required','integer'],
            'permissions'=>['present','array'],
            'permissions.*.module_id'=>['required','integer'],
            'permissions.*.menu_id'=>['required','integer'],
            'permissions.*.child_menu_id'=>['nullable','integer'],
            'permissions.*.can_view'=>['required','boolean'],
            'permissions.*.can_create'=>['required','boolean'],
            'permissions.*.can_update'=>['required','boolean'],
            'permissions.*.can_delete'=>['required','boolean'],
            'extra_permissions'=>['sometimes','array'],
            'extra_permissions.*'=>['integer','distinct']
        ]);

        $target=$this->editable($business,(int)$data['member_id'],$isOwner);
        $modules=$this->tree($business);
        $definitions=$this->definitions($business,$modules);
        $allowedExtraIds=$this->allowedExtras($modules,$definitions);
        $selectedExtras=array_map('intval',$data['extra_permissions']??[]);

        if(array_diff($selectedExtras,$allowedExtraIds)){
            throw ValidationException::withMessages([
                'extra_permissions'=>'Invalid extra permission for this business.'
            ]);
        }

        $expected=[];

        foreach($modules as $module){
            foreach($module->menus as $menu){
                if($menu->children->isEmpty()){
                    $expected[$this->accessKey((int)$module->id,(int)$menu->id,null)]=true;
                }else{
                    foreach($menu->children as $child){
                        $expected[$this->accessKey((int)$module->id,(int)$menu->id,(int)$child->id)]=true;
                    }
                }
            }
        }

        $rows=[];
        $seen=[];
        $now=now();

        foreach($data['permissions'] as $entry){
            $moduleId=(int)$entry['module_id'];
            $menuId=(int)$entry['menu_id'];
            $childId=isset($entry['child_menu_id'])?(int)$entry['child_menu_id']:null;
            $key=$this->accessKey($moduleId,$menuId,$childId);

            if(!isset($expected[$key])||isset($seen[$key])){
                throw ValidationException::withMessages([
                    'permissions'=>'Invalid or duplicate permission entry.'
                ]);
            }

            $seen[$key]=true;
            $view=(bool)$entry['can_view'];

            $rows[]=[
                'business_user_id'=>$target->id,
                'module_id'=>$moduleId,
                'menu_id'=>$menuId,
                'child_menu_id'=>$childId,
                'can_view'=>$view,
                'can_create'=>$view&&(bool)$entry['can_create'],
                'can_update'=>$view&&(bool)$entry['can_update'],
                'can_delete'=>$view&&(bool)$entry['can_delete'],
                'created_at'=>$now,
                'updated_at'=>$now
            ];
        }

        if(count($seen)!==count($expected)){
            throw ValidationException::withMessages([
                'permissions'=>'The complete permission tree is required.'
            ]);
        }

        $target->getConnection()->transaction(function() use($business,$target,$isOwner,$rows,$selectedExtras,$allowedExtraIds,$now){
            $this->editable($business,(int)$target->id,$isOwner,true);

            BusinessUserAccess::query()
                ->where('business_user_id',$target->id)
                ->delete();

            if($rows){
                BusinessUserAccess::query()->insert($rows);
            }

            BusinessUserPermission::query()
                ->where('business_id',$business->id)
                ->where('user_id',$target->id)
                ->whereIn('permission_id',$allowedExtraIds)
                ->delete();

            if($selectedExtras){
                $extraRows=array_map(fn($id)=>[
                    'business_id'=>$business->id,
                    'user_id'=>$target->id,
                    'permission_id'=>$id,
                    'created_at'=>$now,
                    'updated_at'=>$now
                ],$selectedExtras);

                BusinessUserPermission::query()->insert($extraRows);
            }
        });

        return response()->json([
            'message'=>t(
                'Access permissions saved successfully.',
                'অ্যাক্সেস পারমিশন সফলভাবে সংরক্ষিত হয়েছে।'
            )
        ]);
    }
}
