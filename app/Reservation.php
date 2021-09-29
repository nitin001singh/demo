<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $fillable = ['user_id','name','email','phone_number','date','time','person','duration','type_of_booking'];

    public function user(){
        return $this->belongsTo('App\User','user_id','id');
    }
}
