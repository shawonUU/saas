<?php
use App\Models\AuthorityUser;
use App\Models\User;
use App\Models\BusinessRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
Artisan::command('authority:create', function () {
    $name = trim((string) $this->ask('Authority name'));
    $email = strtolower(trim((string) $this->ask('Authority email')));
    $password = (string) $this->secret('Authority password (min 8 characters)');
    $validation = Validator::make(compact('name', 'email', 'password'), [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:authority_users,email', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8'],
    ]);
    if ($validation->fails()) { $this->error($validation->errors()->first()); return; }
    $global = User::create(['name' => $name, 'email' => $email, 'approval_status' => 'Approved']);
    AuthorityUser::create([
        'user_id' => $global->id, 'name' => $name, 'email' => $email,
        'code' => 'AU'.Str::upper(Str::random(12)), 'password' => $password,
        'approval_status' => 'Approved',
    ]);
    $this->info('Authority user created.');
})->purpose('Create an authority login account interactively');

Artisan::command('business:employee {businessCode}', function () {
    $business = \App\Models\Business::where('domain', $this->argument('businessCode'))->first();
    if (!$business) { $this->error('Business not found.'); return; }
    $name = trim((string) $this->ask('Employee name'));
    $email = strtolower(trim((string) $this->ask('Employee email')));
    $password = (string) $this->secret('Employee password (min 8 characters)');
    $validation = Validator::make(compact('name', 'email', 'password'), [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('business_users', 'email')->where('business_id', $business->id)],
        'password' => ['required', 'string', 'min:8'],
    ]);
    if ($validation->fails()) { $this->error($validation->errors()->first()); return; }
    $role = BusinessRole::where('business_id', $business->id)->where('is_owner', false)->first();
    if (!$role) { $this->error('Create an employee role for this business first.'); return; }
    if (User::where('email', $email)->exists()) { $this->error('Global email already exists; use a verified-account linking flow.'); return; }
    $global = User::create(['name' => $name, 'email' => $email, 'approval_status' => 'Approved']);
    \App\Models\BusinessUser::create([
        'user_id' => $global->id, 'business_id' => $business->id, 'role_id' => $role->id, 'name' => $name, 'email' => $email,
        'code' => 'BU'.Str::upper(Str::random(12)), 'password' => $password,
        'approval_status' => 'Approved',
    ]);
    $this->info('Employee login created for '.$business->code.'.');
})->purpose('Create an employee login for an existing business interactively');

require_once __DIR__.'/Public/Business/Admin/Configurations/Products/DemoData/commands.php';
