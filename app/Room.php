<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'rooms';
    protected $fillable = ['room_category','room_shift','price','title','description','image'];

    public function ratingreview(){
        return $this->hasOne('App\Review','item_id','id')->where('item_type','room');
    }

}
