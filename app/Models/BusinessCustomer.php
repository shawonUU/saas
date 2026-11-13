<?php
namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\ErpCrud;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class BusinessCustomer extends Authenticatable
{
    use Notifiable, ErpCrud, SoftDeletes;
    protected $table = 'business_customers';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
    public function business() { return $this->belongsTo(Business::class); }
}
