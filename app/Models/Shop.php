<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
class Shop extends Model
{
    protected $fillable = ['owner_id', 'category_id', 'name', 'code', 'address', 'phone', 'email', 'domain', 'approval_status'];
    protected static function booted(): void
    {
        static::saving(function (self $shop) {
            if (in_array(strtolower((string) $shop->code), config('saas.reserved_subdomains', []), true)) {
                throw ValidationException::withMessages(['code' => 'This subdomain is reserved. Please choose another name.']);
            }
        });
    }
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function category() { return $this->belongsTo(ShopCategory::class, 'category_id'); }
    public function admins() { return $this->hasMany(BusinessUser::class); }
    public function customers() { return $this->hasMany(ShopCustomer::class); }
}
