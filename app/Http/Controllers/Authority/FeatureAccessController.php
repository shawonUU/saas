<?php
namespace App\Http\Controllers\Authority;
use App\Http\Controllers\Controller;
use App\Models\{Business,BusinessCategory,Module,BusinessCategoryModule,BusinessCategoryMenu,BusinessCategoryChildMenu,BusinessModuleOverride,BusinessMenu,BusinessChildMenu};
use App\Services\BusinessNavigation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FeatureAccessController extends Controller
{
    private const CATEGORY = ['module' => [BusinessCategoryModule::class,'module_id'], 'menu' => [BusinessCategoryMenu::class,'menu_id'], 'child' => [BusinessCategoryChildMenu::class,'child_menu_id']];
    private const OVERRIDE = ['module' => [BusinessModuleOverride::class,'module_id'], 'menu' => [BusinessMenu::class,'menu_id'], 'child' => [BusinessChildMenu::class,'child_menu_id']];
    public function categories()
    {
        return view('portals.authority-categories', ['categories' => BusinessCategory::orderBy('name')->get(),
            'businesses' => Business::with('category')->orderBy('name')->get()]);
    }
    public function category(BusinessCategory $category)
    {
        return view('portals.authority-access', ['mode' => 'category','subject' => $category,
            'modules' => Module::with('menus.children')->orderBy('sort_order')->get(),
            'selected' => [
                'module' => BusinessCategoryModule::where('business_category_id',$category->id)->pluck('module_id')->all(),
                'menu' => BusinessCategoryMenu::where('business_category_id',$category->id)->pluck('menu_id')->all(),
                'child' => BusinessCategoryChildMenu::where('business_category_id',$category->id)->pluck('child_menu_id')->all(),
            ]]);
    }
    public function shop(Business $business)
    {
        return view('portals.authority-access', ['mode' => 'shop','subject' => $business->load('category'),
            'modules' => Module::with('menus.children')->orderBy('sort_order')->get(),
            'selected' => [
                'module' => BusinessModuleOverride::where('business_id',$business->id)->pluck('is_enabled','module_id')->all(),
                'menu' => BusinessMenu::where('business_id',$business->id)->pluck('is_enabled','menu_id')->all(),
                'child' => BusinessChildMenu::where('business_id',$business->id)->pluck('is_enabled','child_menu_id')->all(),
            ],'defaults' => [
                'module' => BusinessCategoryModule::where('business_category_id',$business->category_id)->pluck('module_id')->all(),
                'menu' => BusinessCategoryMenu::where('business_category_id',$business->category_id)->pluck('menu_id')->all(),
                'child' => BusinessCategoryChildMenu::where('business_category_id',$business->category_id)->pluck('child_menu_id')->all(),
            ]]);
    }
    private function save(Request $request, string $mode,int $id): void
    {
        $request->validate(['access' => ['nullable','array'],'access.*' => ['array']]);
        $valid = ['module' => \App\Models\Module::pluck('id')->all(),
            'menu' => \App\Models\Menu::pluck('id')->all(),
            'child' => \App\Models\ChildMenu::pluck('id')->all()];
        $map = $mode === 'category' ? self::CATEGORY : self::OVERRIDE;
        $fk = $mode === 'category' ? 'business_category_id' : 'business_id';
        DB::transaction(function () use ($request,$id,$mode,$map,$fk,$valid) {
            foreach ($map as $type => [$class,$column]) {
                $submitted = $request->input('access.'.$type,[]);
                $rows = [];
                foreach ($submitted as $key => $value) {
                    abort_unless(ctype_digit((string)$key) && in_array((int)$key,$valid[$type],true),422);
                    if ($mode === 'shop') {
                        abort_unless(in_array((string)$value,['inherit','1','0'],true),422);
                        if ((string)$value === 'inherit') continue;
                        $rows[] = [$fk => $id,$column => (int)$key,'is_enabled' => (int)$value,'code' => 'X'.\Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(18)),'approval_status' => 'Approved','created_at' => now(),'updated_at' => now()];
                    } elseif ((string)$value === '1') {
                        $rows[] = [$fk => $id,$column => (int)$key,'code' => 'X'.\Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(18)),'approval_status' => 'Approved','created_at' => now(),'updated_at' => now()];
                    } else abort(422);
                }
                // Eloquent delete honors SoftDeletes; force-delete stale mapping rows so unique keys can be reused.
                $class::withTrashed()->where($fk,$id)->forceDelete();
                foreach ($rows as $row) $class::create($row);
            }
        });
    }
    public function saveCategory(Request $request,BusinessCategory $category)
    {
        $this->save($request,'category',$category->id);
        return back()->with('success',t('Category access saved.','ক্যাটাগরি অ্যাক্সেস সংরক্ষিত।'));
    }
    public function saveShop(Request $request,Business $business)
    {
        $this->save($request,'shop',$business->id);
        return back()->with('success',t('Shop overrides saved.','শপ ওভাররাইড সংরক্ষিত।'));
    }
}
