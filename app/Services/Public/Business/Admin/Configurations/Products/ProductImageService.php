<?php
namespace App\Services\Public\Business\Admin\Configurations\Products;
use App\Models\Business;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
class ProductImageService {
 public function upload(UploadedFile $image,Business $business):string {return $image->store('businesses/'.$business->id.'/products','public');}
 public function delete(?string $path,Business $business):void {if($path && str_starts_with($path,'businesses/'.$business->id.'/products/'))Storage::disk('public')->delete($path);}
}
