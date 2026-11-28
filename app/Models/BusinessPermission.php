<?php
namespace App\Models;

use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPermission extends Model
{
    use ErpCrud, SoftDeletes;
    protected $table = 'business_permissions';
    protected $guarded = [];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function module() { return $this->belongsTo(Module::class); }
    public function menu() { return $this->belongsTo(Menu::class); }
    public function childMenu() { return $this->belongsTo(ChildMenu::class); }
}
