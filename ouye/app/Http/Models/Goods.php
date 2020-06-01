<?php

namespace App\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
	protected $table = 'cs_goods';
	protected $dateFormat = 'U';

	public function goodsBrand()
    {
        return $this->hasOne('App\Http\Models\GoodsBrand', 'id', 'brand_id');
    }
	public function address()
    {
        return $this->hasOne('App\Http\Models\Region', 'id', 'region');
    }
	public function goodsCategory()
    {
        return $this->hasOne('App\Http\Models\GoodsCategory', 'id', 'category_id');
    }
}
