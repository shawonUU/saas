<?php
namespace App\Models;
use App\Models\Concerns\ErpCrud;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AuthorityUser extends Authenticatable
{
    use Notifiable;
    use ErpCrud, SoftDeletes;
    protected $table = 'authority_users';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
}
