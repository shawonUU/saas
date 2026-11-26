<?php
namespace App\Models\Configurations;
class DiningTable extends BaseConfiguration
{
    protected $table = 'config_dining_tables';
    public function diningArea(){ return $this->belongsTo(DiningArea::class,'dining_area_id'); }
}
