<?php

namespace App\Http\Controllers\OrderHistory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\OrderHistory;
use App\Transaction;
use App\UserAddress;
use App\Setting;
class OrderHistoryController extends Controller
{
    public function roomOrderHistory()
    {
       $room_order_history = Transaction::with('user')->where('txn_type','ROOM')->get();
       
       if(!empty($room_order_history)){
            foreach($room_order_history as $value){
                if(isset(json_decode($value['checkout_form_data'], true)['selectedaddress'])){
                    $address_id = !isset($value['checkout_form_data']) ? json_decode($value['checkout_form_data'], true)['selectedaddress'] : 0;
                    $deliver_address = UserAddress::select('address')->where('id',$address_id)->first();
                    $value['deliver_address'] = $deliver_address['address'];
                }
            }
        }
       
       return view('orderhistory.index',compact('room_order_history'));
    }
    public function RastaurnatorderHistory()
    {
       $rastro_order_history = Transaction::with('user')->where('txn_type','ITEM')->get();

       
       if(!empty($rastro_order_history)){
            foreach($rastro_order_history as $value){
                if(isset(json_decode($value['checkout_form_data'], true)['selectedaddress'])){
                    $address_id = !isset($value['checkout_form_data']) ? json_decode($value['checkout_form_data'], true)['selectedaddress'] : 0;
                    $deliver_address = UserAddress::select('address')->where('id',$address_id)->first();
                    $value['deliver_address'] = $deliver_address['address'];
                }
            }
        }
       
       return view('orderhistory.index',compact('rastro_order_history'));
    }
  
    public function show($id)
    {
       $order_history = Transaction::with('user')->where('id',$id)->first();
       if($order_history->txn_type == 'ITEM'){
           if(!empty($order_history)){
                if(isset(json_decode($order_history['checkout_form_data'], true)['selectedaddress'])){
                    $address_id = !empty($order_history['checkout_form_data']) ? json_decode($order_history['checkout_form_data'], true)['selectedaddress'] : 0;
                    $deliver_address = UserAddress::select('address')->where('id',$address_id)->first();
                }
           }
           $from_address = Setting::where('key','address')->first();
           $rastro_order_history = $order_history;
           return view('orderhistory.view',compact('rastro_order_history','deliver_address','from_address'));
       }

        if($order_history->txn_type == 'ROOM'){
            $room_order_history = $order_history;
            return view('orderhistory.view',compact('room_order_history'));
        }
    }

    public function RestaurantOrderhistory($id,$status){
        $change = Transaction::where('id',$id)->first();
        $change->status = $status;
        $change->save();
        if($change->status != 0){
            return response()->json(['status'=>200]);
        }else{
            return response()->json(['status'=>500]);
        }
    }
}
