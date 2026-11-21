<?php
namespace App\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class Lists {
 public static function page($query,Request $request,array $columns) {
  $search=trim(mb_substr((string)$request->query('q',''),0,100));
  if($search!=='')$query->where(function($q)use($columns,$search){foreach($columns as $column=>$type)if($type==='text')$q->orWhere($column,'like','%'.$search.'%');});
  $filters=$request->query('f',[]);
  if(is_array($filters))foreach($columns as $column=>$type){$value=$filters[$column]??null;if(!is_scalar($value) || trim((string)$value)==='')continue;$value=mb_substr((string)$value,0,100);
   if($type==='date' && preg_match('/^\d{4}-\d\d-\d\d$/',$value))$query->whereDate($column,$value);
   elseif($type==='number' && is_numeric($value))$query->where($column,$value);
   elseif($type==='text')$query->where($column,'like','%'.$value.'%');
  }
  $sort=(string)$request->query('sort','id');$sort=array_key_exists($sort,$columns)?$sort:'id';$dir=$request->query('dir')==='asc'?'asc':'desc';
  return $query->orderBy($sort,$dir)->paginate(20)->withQueryString();
 }
}
