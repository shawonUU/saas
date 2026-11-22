<?php
namespace App\Services\Public\Business\Admin\Settings\Business\GeneralSettings;
use App\Models\Business;
use App\Models\Public\Business\Admin\Settings\Business\GeneralSettings\BrandSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
class BrandingService {
 public function find(?Business $b): ?BrandSetting {
  if (!$b || !Schema::hasTable('business_brand_settings')) return null;
  return BrandSetting::firstWhere('business_id',$b->id);
 }
 public function imageUrl(?string $path): ?string {
  if (!$path) return null;
  // Older records may store an external URL. New uploads always store local disk paths.
  if (preg_match('~^https?://~i',$path)) return $path;
  if (str_starts_with($path,'/')) return url($path);
  return asset(str_starts_with($path,'storage/')?$path:'storage/'.$path);
 }
 public function store(UploadedFile $file,Business $b,string $role): string {
  return $file->store('businesses/'.$b->id.'/'.$role,'public');
 }
 public function discardOld(?string $path,Business $b):void {
  if ($path && str_starts_with($path,'businesses/'.$b->id.'/')) Storage::disk('public')->delete($path);
 }
}
