<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AuditLog extends Model
{
    use \App\Models\Concerns\ErpCrud;
    protected $guarded = [];
    public $timestamps = false;
    protected function casts(): array { return ['old_values' => 'array', 'new_values' => 'array']; }
}
