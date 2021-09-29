<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = "wallets";
    protected $fillable = ['user_id','amount','message','txn_type','amount_type','txn_data'];

}
