<?php
namespace App\Http\Controllers\Authority;
use App\Http\Controllers\Controller;
use App\Models\{Module,Menu,ChildMenu};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FeatureController extends Controller
{
    private const CLASSES = ['module' => Module::class,'menu' => Menu::class,'child' => ChildMenu::class];
    public function index()
    {
        return view('portals.authority-features', [
            'modules' => Module::with('menus.children')->orderBy('sort_order')->get(),
        ]);
    }
    private function validated(Request $request, string $type, ?int $id = null): array
    {
        abort_unless(isset(self::CLASSES[$type]),404);
        $slugRules = ['required','string','max:150','regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
        if ($type === 'module') $slugRules[] = Rule::unique('modules','slug')->ignore($id);
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'name_bn' => ['nullable','string','max:150'],
            'slug' => $slugRules,
            'icon' => ['nullable','string','max:100'],
            'sort_order' => ['nullable','integer','min:0','max:10000'],
            'is_active' => ['required','boolean'],
            'module_id' => [$type === 'menu' ? 'required' : 'nullable','integer',Rule::exists('modules','id')->whereNull('deleted_at')],
            'menu_id' => [$type === 'child' ? 'required' : 'nullable','integer',Rule::exists('menus','id')->whereNull('deleted_at')],
            'route' => ['nullable','string','max:255','regex:/^\/[a-zA-Z0-9\/_-]*$/'],
            'permission' => ['nullable','string','max:150'],
        ]);
        if ($type === 'menu') {
            $request->validate(['slug' => [Rule::unique('menus','slug')->where('module_id',$data['module_id'])->ignore($id)]]);
        }
        if ($type === 'child') {
            $request->validate(['slug' => [Rule::unique('child_menus','slug')->where('menu_id',$data['menu_id'])->ignore($id)]]);
        }
        return array_intersect_key($data,array_fill_keys(match ($type) {
            'module' => ['name','name_bn','slug','icon','sort_order','is_active'],
            'menu' => ['name','name_bn','slug','icon','sort_order','is_active','module_id','route','permission'],
            'child' => ['name','name_bn','slug','icon','sort_order','is_active','menu_id','route','permission'],
        },true));
    }
    public function store(Request $request, string $type)
    {
        $class = self::CLASSES[$type] ?? abort(404);
        $class::create([...$this->validated($request,$type),'approval_status' => 'Approved']);
        return back()->with('success',t('Feature created.','ফিচার তৈরি হয়েছে।'));
    }
    public function update(Request $request, string $type, int $id)
    {
        $class = self::CLASSES[$type] ?? abort(404);
        $class::findOrFail($id)->update($this->validated($request,$type,$id));
        return back()->with('success',t('Feature updated.','ফিচার আপডেট হয়েছে।'));
    }
    public function destroy(string $type,int $id)
    {
        $class = self::CLASSES[$type] ?? abort(404);
        $item = $class::findOrFail($id);
        $hasChildren = match ($type) {
            'module' => $item->menus()->exists(),
            'menu' => $item->children()->exists(),
            default => false,
        };
        if ($hasChildren) return back()->withErrors(['feature' => 'Remove child items before deleting a parent.']);
        // Do not delete access mappings unexpectedly. Disable instead.
        $item->update(['is_active' => false]);
        return back()->with('success',t('Feature disabled.','ফিচার বন্ধ করা হয়েছে।'));
    }
}
