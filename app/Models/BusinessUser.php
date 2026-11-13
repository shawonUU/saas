<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class BusinessUser extends Authenticatable
{
    use Notifiable, ErpCrud, SoftDeletes;
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
    public function business() { return $this->belongsTo(Business::class); }
    public function role() { return $this->belongsTo(BusinessRole::class); }
    public function accesses() { return $this->hasMany(BusinessUserAccess::class); }
    public function isOwnerOf(Business $business): bool
    {
        return (int)$this->business_id === (int)$business->id
            && (int)$this->user_id === (int)$business->owner_id
            && (bool) $this->role?->is_owner;
    }
}
