<?php
namespace App\Services\Public\Business\Admin\Shared;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class ListPage
{
    public static function page(Builder $query, Request $request, array $columns, string $title, int $default=15)
    {
        $filters=$request->query('f',[]);
        if(is_array($filters)) foreach($columns as $field=>$type) {
            $value=$filters[$field]??null;
            if(!is_scalar($value)) continue;
            $value=trim(mb_substr((string)$value,0,100));
            if($value==='')continue;
            if($type==='number' && is_numeric($value))$query->where($field,$value);
            elseif($type==='date' && preg_match('/^\d{4}-\d{2}-\d{2}$/',$value))$query->whereDate($field,$value);
            elseif($type==='text')$query->where($field,'like','%'.$value.'%');
        }
        $sort=(string)$request->query('sort','');
        if(isset($columns[$sort]))$query->reorder()->orderBy($sort, $request->query('dir')==='asc'?'asc':'desc');
        if(in_array($request->query('export'), ['excel','pdf'],true)) {
            throw new HttpResponseException(app(ListExportService::class)->download($query, $columns, $title, (string)$request->query('export')));
        }
        $perPage=(int)$request->query('per_page',$default);
        if(!in_array($perPage,[10,15,25,50,100],true))$perPage=$default;
        return $query->paginate($perPage)->withQueryString();
    }
}
