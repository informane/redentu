<?php

namespace App\Http\Controllers;

use App\Imports\GoodsImport;
use App\Models\Goods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class GoodsController extends Controller{
    public function index(){
        $goods=Goods::with(['categories'=>function($query){
            $query->select('id', 'rubric', 'name');
        }])->select(['cat_id', 'manufacturer', 'name', 'desc', 'price', 'warranty'])->get();
        $goods2=[];
        foreach ($goods as $key=>$good){
            $goods2[$key]=$good;
            unset($goods2[$key]->cat_id);
            $goods2[$key]->rubric=$good->categories->rubric;
            $goods2[$key]->cat_name=$good->categories->name;
            unset($goods2[$key]->categories);
        }
        return Inertia::render('Goods', ['goodsProp'=>$goods2]);
    }

    public function import(Request $request){

        $maxFileSize=ini_get('upload_max_filesize');
        if (substr($maxFileSize, -1) == 'K'){
            $maxFileSize=(int) substr($maxFileSize, 0, -1);
        } else if (substr($maxFileSize, -1) == 'M'){
            $maxFileSize=1024*(int) substr($maxFileSize, 0, -1);
        } else if (substr($maxFileSize, -1) == 'G'){
            $maxFileSize=1024*1024*(int) substr($maxFileSize, 0, -1);
        }
        //var_dump($maxFileSize);
        $valid=$request->validate([
            'file'=>"required|mimes:xlsx|max:$maxFileSize"
        ]);

        if (empty($valid['message'])){

            Excel::import(new GoodsImport, $request->file('file'));

        } else {
            return response()->json($valid);
        }
    }

    public function clear()
    {
        DB::table('goods')->delete();
        DB::table('categories')->delete();
        return redirect('/goods');
    }
}
