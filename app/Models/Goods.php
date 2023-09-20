<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Goods extends Model
{
    protected $fillable = ['cat_id','name', 'cat_name','manufacturer','sku','desc','price','warranty', 'exists'];
    use HasFactory;

    public function categories(): BelongsTo
    {
        return $this->belongsTo(Categories::class,'cat_id','id');
    }
}
