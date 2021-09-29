<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Restaurant extends Model
{
    protected $table = 'restaurants';
	protected $fillable = ['title','image','description','old_price','new_price','category_name'];

	public function rstroreviewrating(){
		return $this->hasMany('App\Review','item_id','id')->where('item_type','restaurant');
	}
}
