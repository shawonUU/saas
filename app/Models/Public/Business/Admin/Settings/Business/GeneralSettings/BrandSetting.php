<?php
namespace App\Models\Public\Business\Admin\Settings\Business\GeneralSettings;
use Illuminate\Database\Eloquent\Model;
class BrandSetting extends Model { use \App\Models\Concerns\ErpCrud;
 protected $table='business_brand_settings';
 protected $fillable=['business_id','favicon','banner','secondary_banner','primary_color','tagline','about'];
 public function business(){return $this->belongsTo(\App\Models\Business::class);}
}
