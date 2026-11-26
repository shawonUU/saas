<?php
namespace App\Http\Controllers\Public\Business\Admin\Configurations\DiningAreas;
use App\Http\Controllers\Public\Business\Admin\Configurations\RestaurantMasterController;
use App\Models\Configurations\DiningArea;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use App\Models\Configurations\DiningTable;

class DiningAreaController extends RestaurantMasterController
{
    protected const SLUG = 'dining-areas';
    protected const TITLE = 'Dining Areas';
    protected const TITLE_BN = 'ডাইনিং এরিয়া';
    protected const MODEL = DiningArea::class;
    protected const FIELDS = [
        'name' => ['label'=>'Name','label_bn'=>'নাম','type'=>'text','required'=>true,'options'=>[]],
        'name_bn' => ['label'=>'Bangla Name','label_bn'=>'বাংলা নাম','type'=>'text','required'=>false,'options'=>[]],
        'description' => ['label'=>'Description','label_bn'=>'বিবরণ','type'=>'textarea','required'=>false,'options'=>[]],
    ];
    protected function beforeDelete(Model $row): void
    {
        if (DiningTable::where('business_id',$row->business_id)->where('dining_area_id',$row->id)->exists())
            throw ValidationException::withMessages(['name'=>t('Remove or move the dining tables first.','আগে টেবিলগুলো সরান অথবা স্থানান্তর করুন।')]);
    }
    protected function beforeUpdate(Model $row, array $data): void
    {
        if (!$data['is_active'] && DiningTable::where('business_id',$row->business_id)->where('dining_area_id',$row->id)->where('is_active',true)->exists())
            throw ValidationException::withMessages(['name'=>t('Deactivate or move active tables first.','আগে সক্রিয় টেবিলগুলো নিষ্ক্রিয় বা স্থানান্তর করুন।')]);
    }
}
