<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;
class Business extends Model
{
    use ErpCrud, SoftDeletes;
    protected $guarded = [];
    protected static function booted(): void
    {
        static::saving(function (self $business) {
            if (in_array(strtolower((string) $business->code), config('saas.reserved_subdomains', []), true)) {
                throw ValidationException::withMessages(['code' => 'This subdomain is reserved. Please choose another name.']);
            }
        });
    }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function category() { return $this->belongsTo(BusinessCategory::class, 'category_id'); }
    public function admins() { return $this->hasMany(BusinessUser::class); }
    public function customers() { return $this->hasMany(BusinessCustomer::class); }
    public function roles() { return $this->hasMany(BusinessRole::class); }
}
