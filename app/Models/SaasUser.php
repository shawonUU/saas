<?php

namespace App\Models;
use App\Models\Concerns\ErpCrud;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SaasUser extends Authenticatable
{
    use Notifiable, ErpCrud, SoftDeletes;
    protected $table = 'saas_users';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token',];
    protected function casts(): array { return ['password' => 'hashed', 'email_verified_at' => 'datetime']; }
}
