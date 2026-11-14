<?php

use App\Models\Business;

if (!function_exists('approvalStatus')) {
    function approvalStatus(): array { return ['Approved', 'Pending', 'Rejected', 'Cancelled']; }
}
if (!function_exists('t')) {
    function t(string $english, string $bangla): string {
        return session('portal_lang', 'en') === 'bn' ? $bangla : $english;
    }
}

if(!function_exists('currentBusiness')){
    function currentBusiness():?Business{
        return request()->attributes->get('currentBusiness');
    }
}

if(!function_exists('getGenerateCode')){
    function getGenerateCode($model, $column,  $prefix, $numbers = 8,$noTrash = null){
        $lastRecord = $model::orderBy('id', 'desc')->first();

        if (!$lastRecord) {return $prefix . '-1';}
        $lastCode = $lastRecord->{$column};
        $number = (int) str_replace($prefix . '-', '', $lastCode);
        return $prefix . '-' . ($number + 1);
    }
}

if (!function_exists('navName')) {
    function navName($item): string {
        return session('portal_lang','en') === 'bn' && filled($item->name_bn) ? $item->name_bn : $item->name;
    }
}


if(!function_exists('currentBusiness')){
    function currentBusiness():?Business{
        return request()->attributes->get('currentBusiness');
    }
}
