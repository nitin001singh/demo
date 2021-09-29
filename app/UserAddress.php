<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $table = "user_addresses";
    protected $fillable = ["user_id",'address','category','floor','to_address','how_reach'];

}
