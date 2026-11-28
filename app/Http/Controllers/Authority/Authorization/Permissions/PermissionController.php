<?php
namespace App\Http\Controllers\Authority\Authorization\Permissions;

use App\Http\Controllers\Controller;
use App\Models\{BusinessPermission, BusinessUserPermission, ChildMenu, Menu, Module};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim(mb_substr((string) $request->query('search', ''), 0, 100));
        $status = (string) $request->query('status', '');
        $query = BusinessPermission::with(['module','menu','childMenu']);
        if ($search !== '') $query->where(fn ($q) => $q->where('name','like','%'.$search.'%')->orWhere('key','like','%'.$search.'%'));
        if (in_array($status,['0','1'],true)) $query->where('is_active', $status === '1');
        $records = $query->latest('id')->paginate(20)->withQueryString();
        $modules = Module::with('menus.children')->orderBy('sort_order')->get();
        return view('authority.admin.authorization.permissions.index', compact('records','modules','search','status'));
    }

    private function data(Request $request, ?BusinessPermission $permission = null): array
    {
        $request->merge(['name'=>trim((string) $request->input('name')), 'key'=>trim((string) $request->input('key'))]);
        $unique = Rule::unique('business_permissions', 'key');
        if ($permission) $unique->ignore($permission->id);
        $data = $request->validate([
            'name'=>['required','string','max:255'],
            'key'=>['required','string','max:255','regex:/^[a-z][a-z0-9._:-]*$/', $unique],
            'description'=>['nullable','string','max:2000'],
            'module_id'=>['required','integer',Rule::exists('modules','id')->whereNull('deleted_at')],
            'menu_id'=>['nullable','integer',Rule::exists('menus','id')->whereNull('deleted_at')],
            'child_menu_id'=>['nullable','integer',Rule::exists('child_menus','id')->whereNull('deleted_at')],
            'is_active'=>['required','boolean'],
        ]);
        if (!empty($data['menu_id']) && !Menu::whereKey($data['menu_id'])->where('module_id',$data['module_id'])->exists()) {
            throw ValidationException::withMessages(['menu_id'=>t('Menu must belong to this module.','মেনু এই মডিউলের হতে হবে।')]);
        }
        if (!empty($data['child_menu_id']) && (empty($data['menu_id']) || !ChildMenu::whereKey($data['child_menu_id'])->where('menu_id',$data['menu_id'])->exists())) {
            throw ValidationException::withMessages(['child_menu_id'=>t('Child menu must belong to this menu.','চাইল্ড মেনু এই মেনুর হতে হবে।')]);
        }
        // The key is globally unique, including records in the soft-delete archive.
        if (BusinessPermission::withTrashed()->where('key',$data['key'])->when($permission,fn($q)=>$q->whereKeyNot($permission->id))->exists()) {
            throw ValidationException::withMessages(['key'=>t('Permission key already exists, including archived permissions.','এই পারমিশন কি আগে থেকেই আছে, আর্কাইভেও থাকতে পারে।')]);
        }
        $data['menu_id'] = $data['menu_id'] ?? null;
        $data['child_menu_id'] = $data['child_menu_id'] ?? null;
        return $data;
    }

    public function store(Request $request)
    {
        $data = $this->data($request);
        BusinessPermission::create($data + ['approval_status'=>'Approved','approved_at'=>now(),'approved_by'=>Auth::guard('authority')->id()]);
        return back()->with('success',t('Global permission created.','গ্লোবাল পারমিশন তৈরি হয়েছে।'));
    }

    public function update(Request $request, int $record)
    {
        $permission = BusinessPermission::findOrFail($record);
        $data = $this->data($request,$permission);
        if (BusinessUserPermission::where('permission_id',$permission->id)->exists()) {
            foreach (['key','module_id','menu_id','child_menu_id'] as $field) {
                if ((string) ($permission->$field ?? '') !== (string) ($data[$field] ?? '')) {
                    throw ValidationException::withMessages([$field=>t('Assigned permission identity cannot be changed. Remove user grants first.','এই পারমিশন ইউজারদের দেওয়া আছে। আগে ইউজার গ্রান্ট সরিয়ে পরিচয় পরিবর্তন করুন।')]);
                }
            }
        }
        $permission->update($data);
        return back()->with('success',t('Global permission updated.','গ্লোবাল পারমিশন আপডেট হয়েছে।'));
    }

    public function destroy(int $record)
    {
        $permission = BusinessPermission::findOrFail($record);
        if (BusinessUserPermission::where('permission_id',$permission->id)->exists()) {
            return back()->withErrors(['delete'=>t('Permission is assigned to users. Remove grants first.','পারমিশন ইউজারদের দেওয়া আছে। আগে বরাদ্দ সরান।')]);
        }
        $permission->delete();
        return back()->with('success',t('Global permission deleted.','গ্লোবাল পারমিশন মুছে ফেলা হয়েছে।'));
    }
}
