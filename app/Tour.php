<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
     protected $table = "tours";
     protected $fillable = ['slug','title','description','caption','category','price','image'];
}
