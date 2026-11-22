<?php
use Database\Seeders\EcommerceDemoSeeder;
use Illuminate\Support\Facades\Artisan;
Artisan::command('ecommerce:seed-demo {businessId : Existing business ID} {--force : Allow production demo seeding}',function(){
 $seeder=app(EcommerceDemoSeeder::class);
 $id=filter_var($this->argument('businessId'),FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
 if(!$id){$this->error('Please provide a valid business ID.');return 1;}
 if(app()->environment('production')&&!$this->option('force')){
  $this->error('Demo data on production is blocked. Use --force only for a non-live test business.');return 1;
 }
 try{$result=$seeder->seedBusiness((int)$id);}
 catch(\Throwable $e){$this->error('Demo seed failed: '.$e->getMessage());return 1;}
 foreach($result as $name=>$value)$this->line(str_replace('_',' ',$name).': '.$value);
 $this->info('Demo catalog ready. Run php artisan storage:link once; then open the business storefront.');
 return 0;
})->purpose('Seed an individual business with demo categories, products, stock and local images');
Artisan::command('ecommerce:demo-businesses',function(){
 $rows=\App\Models\Business::query()->select('id','name','domain')->orderBy('id')->get();
 if($rows->isEmpty()){$this->warn('No businesses yet. Create a business first.');return 0;}
 $this->table(['ID','Business','Subdomain'],$rows->map(fn($b)=>[$b->id,$b->name,$b->domain])->all());
 return 0;
})->purpose('List available business IDs for ecommerce demo seeding');
