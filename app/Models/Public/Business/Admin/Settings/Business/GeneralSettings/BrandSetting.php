<?php
namespace App\Models\Public\Business\Admin\Settings\Business\GeneralSettings;
use Illuminate\Database\Eloquent\Model;
class BrandSetting extends Model {
 protected $table='business_brand_settings';
 protected $fillable=['business_id','favicon','banner','secondary_banner','primary_color','tagline','about'];
 public function business(){return $this->belongsTo(\App\Models\Business::class);}
}
