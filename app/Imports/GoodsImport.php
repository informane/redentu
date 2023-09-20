<?php

namespace App\Imports;

use App\Events\ImportMsg;
use App\Models\Categories;
use App\Models\Goods;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class GoodsImport implements ToModel, WithBatchInserts, WithChunkReading, WithEvents
{

    private $addedNumber;
    private $skippedNumber;
    private $goods;
    private $skus;
    private $categories;
    private $categoryNames;

    public function __construct(){
        $this->addedNumber=0;
        $this->skippedNumber=0;
        $this->goods=[];
        $this->skus=[];
        $this->categories=[];
        $this->categoryNames=[];
    }

    public function model(array $row){
        if (empty($row[0]) && empty($row[1])){
            $offset=1;
            $rubric=$row[1+$offset];

        } else if (empty($row[0])){
            if (!empty($row[10])){
                $offset=1;
                $rubric=$row[0+$offset] . ', ' . $row[1+$offset];
            } else {
                $offset=0;
                $rubric=$row[1];
            }
        } else {
            $offset=0;
            $rubric=$row[0] . ', ' . $row[1];
        }

        $row[2+$offset]=trim($row[2+$offset]);
        $row[4+$offset]=trim($row[4+$offset]);
        $row[5+$offset]=trim($row[5+$offset]);
        if(!in_array($row[2+$offset],$this->categoryNames)){
            $this->categoryNames[]=$row[2+$offset];
            $this->categories[]=[
                'rubric'=>$rubric, 'name'=>$row[2+$offset]
            ];
        }
        /*if (!empty($row[2+$offset])){
            $category=Categories::firstOrCreate(['rubric'=>$rubric, 'name'=>$row[2+$offset]]);
        }*/
        /*
DELETE FROM `goods` WHERE 1;
delete FROM `categories` WHERE 1
        */

        if (!in_array($row[5+$offset], $this->skus) && !in_array($row[4+$offset], $this->goods) && !empty($row[4+$offset]) && $row[4+$offset] != 'Наименование товара'){
            $this->addedNumber++;
            if($this->addedNumber%50==0){
                ImportMsg::dispatch($this->addedNumber, $this->skippedNumber);
            }

            $good=new Goods([
                'cat_id'      =>$category->id ?? null,
                'manufacturer'=>$row[3+$offset],
                'name'        =>$row[4+$offset],
                'cat_name'    =>$row[2+$offset],
                'sku'         =>$row[5+$offset],
                'desc'        =>$row[6+$offset],
                'price'       =>(float) $row[7+$offset],
                'warranty'    =>(int) $row[8+$offset],
                'exists'      =>$row[9+$offset] == 'есть в наличие'
            ]);
            $this->goods[]=$row[4+$offset];
            $this->skus[]=$row[5+$offset];
            return $good;
        } else {
            if ($row[4+$offset] != 'Наименование товара'){
                $this->skippedNumber++;
                if($this->skippedNumber%100==0){
                    ImportMsg::dispatch($this->addedNumber, $this->skippedNumber);
                }
            }
            return null;
        }
    }

     public function registerEvents(): array
     {

         return [
             AfterImport::class => function(AfterImport $event) {
                 Categories::insert($this->categories);

                 DB::table('goods')->update(['cat_id'=>DB::raw('(select id from categories where name=goods.cat_name)')]);
                 echo "Import Successfull!\nTotal imported: {$this->addedNumber}\ntotal skipped: {$this->skippedNumber}";
             },

         ];

     }

    public function batchSize()
    : int{
        return 1000;
    }

    public function chunkSize()
    : int{
        return 1000;
    }
}
