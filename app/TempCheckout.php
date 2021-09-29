<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TempCheckout extends Model
{
     protected $table = "temp_checkouts";
     protected $fillable = ['formData'];
}
