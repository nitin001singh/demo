<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use Mail;
use App\Mail\SendToAdminMail;
use App\Mail\SendToUserMail;
use App\Book;
use App\Newsletter;
use App\Room;
use App\Guest;
use App\Event;
use App\Setting;
use App\User;
use App\TempCheckout;
use App\Transaction;
use App\Wallet;
use App\NotificationPreferece;
use View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Mail\UserRegisterDetailMail;


class Frontpage2Controller extends Controller
{  
    function registerUser($userdata,$randomString){
        if($userdata){
            $first_name = explode(' ',$userdata['customerName'])[0];
            $last_name = explode(' ',$userdata['customerName'])[1];

            $insert = new User();
            $insert->salutation=$userdata['salutation'];
            $insert->username= strtolower($first_name).'-'.strtolower($last_name).'-'.random_int(100000,999999);
            $insert->first_name=ucfirst($first_name);
            $insert->last_name = ucfirst($last_name);
            $insert->gender = $userdata['customerGender'];
            $insert->email = $userdata['customerEmail'];
            $insert->phone_number = '+91'.$userdata['customerPhone'];
            $insert->country = 101;//india
            $insert->state = 33;//rajasthan
            $insert->city = 3378;//jaipur 
            $insert->email_verifid_token = Str::random(20);
            $insert->referral_code = strtolower(Str::random(6));
            $insert->password = bcrypt($randomString);
            $insert->save();   

            $wallet = new Wallet();
            $wallet->user_id = $insert->id;
            $wallet->amount = env('SELF_SIGNUP_AMOUNT');
            $wallet->message = "Account Signup Amount";
            $wallet->txn_type = 'credit';
            $wallet->amount_type = 'TTI_REWARD';
            $wallet->save();

            $activity_array = ['reviews','follow','friends_join','up_tti','weeknesltr','prc_sett'];
            $title_array = ['Activity on my reviews','Someone follows me','My friends join TTI','Important updates from TTI','Weekly Newsletter','Privacy Setting'];

            foreach($activity_array as $key => $value){
                $prference = new NotificationPreferece();
                $prference->user_id = $insert->id;
                $prference->activity = $value;
                $prference->title = $title_array[$key];
                $prference->is_email = 'Yes';
                $prference->is_phone = 'Yes';
                $prference->is_active = 'Yes';
                $prference->save();
            }

            Mail::to($insert['email'])->send(new UserRegisterDetailMail($insert,$randomString));
            return [$insert,$randomString];
        }
    }


    public function saveRoomTempData(Request $request){
        $checkout_form_data = $request->all();
         
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 3; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        $randomString = $randomString.'!@ABC';

        if(!empty($checkout_form_data)){

            $user = User::where('email',$checkout_form_data['customerEmail'])->first();
            if(!$user){

                $userdata = array(
                    'salutation'=>$checkout_form_data['salutation'],
                    'customerName'=>$checkout_form_data['customerName'],
                    'customerEmail'=>$checkout_form_data['customerEmail'],
                    'customerPhone'=>$checkout_form_data['customerPhone'],
                    'customerGender'=> $checkout_form_data['gender'],
                );

                $user_save_data = $this->registerUser($userdata,$randomString);

                Auth::loginUsingId($user_save_data[0]->id, true);

            }
            else{
                Auth::loginUsingId($user->id, true);
                // if(!Auth::check()){
                //     return response()->json(['status'=>'signin_error','already_signin'=>'Please Login In to Continue.']);
                // } 
            }
             
            //store temp data
            if(Auth::check()){
                $data  = [];
                $data['totalAmount'] = $checkout_form_data['totalAmount'];
                $data['txn_type'] = 'ROOM';
                $data['user_id'] =(!Auth::check()) ? 0 : Auth::user()->id ;
                $data['customerName'] = $checkout_form_data['customerName'];
                $data['customerEmail'] = $checkout_form_data['customerEmail'];
                $data['customerPhone'] = $checkout_form_data['customerPhone'];
                $data['checkin'] = $checkout_form_data['checkin'];
                $data['checkout'] = $checkout_form_data['checkout'];

                $data['item']['room_id'] = $checkout_form_data['room_id'];
                $data['item']['room_category'] = $checkout_form_data['room_category'];
                $data['item']['room_shift'] = $checkout_form_data['room_shift'];
                $data['item']['per_shift_price'] = $checkout_form_data['per_shift_price'];
                $data['item']['room_title'] = $checkout_form_data['room_title'];
                $data['item']['room'] = $checkout_form_data['room'];
                $data['item']['guest'] = $checkout_form_data['guest'];
                $data['item']['room_image'] = $checkout_form_data['room_image'];

                $temp_checkout = new TempCheckout();
                $record_is_exist = TempCheckout::where('user_id',$data['user_id'])->first();
                if(!empty($record_is_exist)){
                  TempCheckout::where('user_id',$data['user_id'])->delete();
              }
              $temp_checkout->user_id = $data['user_id'];
              $temp_checkout->formData = json_encode($data);
              $temp_checkout->save();
                return response()->json(['status'=>'success','last_inserted_id'=>$temp_checkout->id]);
            }
            return response()->json(['status'=>'success','last_inserted_id'=>0]);
          
      }else{
          return response()->json(['status'=>'error','last_inserted_id'=> 0]);
      }

  }

  public function submitBookNow(Request $request){
 
      $data = $request->all();
      $checkin = $data['checkin'];
      $checkout = $data['checkout'];
      $room_category_filter = $data['room_category_filter'];
      $room_id = $data['room_id'];
      $guest = $data['guest'];

      $total_needed_room = $data['room'];

      $settingdata = Setting::where('key',$room_category_filter)->first();

      $per_room_person = Setting::where('key','per_room_person')->first();

      $total_persons = $per_room_person->value * $total_needed_room;
      if($settingdata && $per_room_person && $total_persons >= $guest && $settingdata->value >= $total_needed_room){

       if($checkin == $checkout){
          $checkin_time = date('Y-m-d',strtotime($checkin.' 12:00:00 PM'));
          $checkout_time = date('Y-m-d',strtotime($checkout.' 12:00:00 PM') + 86400);
      }else{
          $checkin_time = date('Y-m-d',strtotime($checkin.' 12:00:00 PM'));
          $checkout_time = date('Y-m-d',strtotime($checkout.' 12:00:00 PM'));
      }

      $status = array(0,2,3);
        // get all bookings that are free and not approve
      $booking_data = Book::where('room_id',$room_id)->get();
      $id_arr = array();
      if(!$booking_data->isEmpty()){
        foreach($booking_data as $key => $value){
            if(in_array($value->status,$status)){
              if($checkin_time <= $value->cin_date  && $checkout_time <= $value->cin_date ){
                array_push($id_arr, $value->id);
            }else if($checkin_time >= $value->cout_date && $checkout_time > $value->cout_date ){
                array_push($id_arr, $value->id);
            }else{
            }
        }
    }

    if($id_arr != []){ 
        return response()->json(['status'=>'available']);
    }else{
        return response()->json(['status'=>'un-available']);
    }
}else{ 
    return response()->json(['status'=>'available']);
}
}else{
    return response()->json(['status'=>'un-available']);
}
}

public function generateRoomSignature(Request $request){
    if(env('CASHFREE_TEST_MODE')){
        $secretKey = env('CASHFREE_SECRET_KEY_TEST');
        $appId = env('CASHFREE_APP_ID_TEST');
    }else{
        $secretKey = env('CASHFREE_SECRET_KEY_LIVE');
        $appId = env('CASHFREE_APP_ID_LIVE');
    }

    $merchantData = array(
     "last_inserted_id" => $request->last_inserted_id,
    );
    $merchantData = base64_encode(json_encode($merchantData));
    $postData = array(
      "appId" => $appId,
      "orderId" => $request->last_inserted_id,
      "orderAmount" => $request->orderAmount,
      "orderCurrency" => 'INR',
      "orderNote" => '',
      "customerName" => $request->customerName,
      "customerEmail" => $request->customerEmail,
      "customerPhone" => $request->customerPhone,
      "merchantData" => $merchantData,  
      "returnUrl" => env('APP_URL').'room-checkout-payment',
      "notifyUrl" => '',
      "gender" => $request->gender,
      "salutation" => $request->salutation,
      "category" => $request->room_category,
      "room_id" => $request->room_id,
      "first_name" => $request->customerFirstName,
      "last_name" => $request->customerlastName,
      "checkin" => $request->checkInDate,
      "checkout" => $request->checkOutDate,
      "guest" => $request->guest,
      "room" => $request->room,
      "email" => $request->email,
      "phone" => $request->phone,
      "accepttc" => $request->tandc,
    );

             // get secret key from your config
    ksort($postData);
    $signatureData = "";
    foreach ($postData as $key => $value){
      $signatureData .= $key.$value;
    }
    $signature = hash_hmac('sha256', $signatureData, $secretKey,true);
    $signature = base64_encode($signature);

    $opt['status'] = 'success';
    $opt['signature'] = $signature;
    $opt['merchantData'] = $merchantData;
    echo json_encode($opt); die;
}

public function roomCheckoutPayment(Request $request){
    $data = $request->all();
    if(!empty($data) && ($data['txStatus'] == 'SUCCESS')){
    
      $temp_checkout_inserted_data = explode('-',$data['orderId']);
      $temp_checkout_inserted_id = trim($temp_checkout_inserted_data[1]);
      $checkout_records = TempCheckout::where('id', $temp_checkout_inserted_id)->first();
      $checkout_form_data = !empty($checkout_records) ? json_decode($checkout_records['formData'], true) : array(); 

      if($checkout_form_data){

            //book table
            if($checkout_form_data['item']['room_category'] == "suite")
                $category = 1;
            else if($checkout_form_data['item']['room_category'] == "deluxe")
                $category = 2;
            else if($checkout_form_data['item']['room_category'] == "couple")
                $category = 3;
            else
                $category = 4;

            $book = new Book();
            $book->user_id = $checkout_form_data['user_id'];
            $book->room_id = $checkout_form_data['item']['room_id'];
            $book->cin_date = $checkout_form_data['checkin'];
            $book->cout_date = $checkout_form_data['checkout'];
            $book->category = $category;
            $book->room = $checkout_form_data['item']['room'];
            $book->guests = $checkout_form_data['item']['guest'];
            $book->name = $checkout_form_data['customerName'];
            $book->email = $checkout_form_data['customerEmail'];
            $book->phone_number = '+91'.$checkout_form_data['customerPhone'];
            $book->save();

            //decrease value by 1 accoding to category
            $settingdata = Setting::where('key',$checkout_form_data['item']['room_category'])->first();
            $settingdata->value = $settingdata->value - $checkout_form_data['item']['room'];
            $settingdata->save();
      }

      $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 10);
      $txn_rec = new Transaction();
      $txn_rec->user_id = $checkout_form_data['user_id'];
      $txn_rec->txnid = $txnid;
      $txn_rec->amount = $data['orderAmount'];
      $txn_rec->txn_type = $checkout_form_data['txn_type'];
      $txn_rec->checkout_form_data = json_encode($checkout_form_data);
      $txn_rec->payu_data =  json_encode($data);
      $txn_rec->status =  'D';
      $txn_rec->save();

      TempCheckout::where('id',$temp_checkout_inserted_id)->delete();
      return view('roompaymentsuccess',compact('txn_rec'));


  }else{
      return view('paymenterror');
  }
}
 
public function changeStatusFreeRoom()
{
    $booking = Book::where('status',1)->where('is_free',0)->get();
    if(!$booking->isEmpty()){
        foreach($booking as $key => $value){
            $checkout_date = date('Y-m-d',strtotime($value->cout_date.' 12:00:00 PM'));
            $current_date  = date('Y-m-d ',strtotime('12:00:00 PM'));  
            if($current_date >= $checkout_date){
                //1 for suit,2 for delux room,3 for for couple,4 for family
                if($value->category == 1)
                    $category = "suite";
                else if($value->category == 2)
                    $category = "deluxe";
                else if($value->category == 3)
                    $category = "couple";
                else
                    $category = "family";

                $settingdata = Setting::where('key',$category)->first();
                $settingdata->value = $value->room + $settingdata->value;
                $settingdata->save();

                $value->is_free = 1;
                $value->save();
            }
        }
    }


}

public function getRoomRates(Request $request)
{

      //1 for suit,2 for delux room,3 for for couple,4 for family
    if(strtolower($request['category']) == "family"){
        $room_available = Setting::where('key','family_room')->first();
        $category = 4;
    }else if(strtolower($request['category']) == "deluxe"){
        $room_available = Setting::where('key','deluxe_room')->first();
        $category = 2;
    }else if(strtolower($request['category']) == "couple"){
        $room_available = Setting::where('key','couple_room')->first();
        $category = 3;
    }else{
        $room_available = Setting::where('key','suite_room')->first();
        $category = 1;
    }

    $total_room_available = $room_available->value;
        //$per_room_persons = $room_persons->value;

    if($request['checkin'] != null || $request['checkout'] != null)
      $room_reserve = Book::whereIn('status',[2,3])->whereDate('cin_date','>=',$request['checkin'])->whereDate('cout_date','<=',$request['checkout'])->where('category',$category)->get();
  else
      $room_reserve = Book::whereIn('status',[2,3])->where('category',$category)->get();

  if($total_room_available >= $request['room'] && $room_reserve->count() > 0){
    $allRoom = [];
    foreach($room_reserve as $key => $value){
      array_push($allRoom,$value->room_id);
  }
  $rooms = Room::with('ratingreview')->whereIn('id',$allRoom)->orderBy('id','desc')->get();
}

$roomcategory = Setting::where("key","room_category")->first();
if($roomcategory){
    $roomcategory = explode(',', $roomcategory->value);
    return view('room-rates',compact('rooms','roomcategory'));
}
else{
    return view('room-rates',compact('rooms'));
}
}
  //new functions


  public function cancelReservation(Request $request){
    
    $data = $request->all();
    if(!empty($data)){
        $txnid = $data['txnid'];
        $trans_data = Transaction::where('txnid', $txnid)->first();
        if(!empty($trans_data)){
            $checkout_data = json_decode($trans_data->checkout_form_data, true);
            $payudata_data = json_decode($trans_data->payu_data, true);
            
            $checkin  = strtotime($checkout_data['checkin']." 12:00:00");
            $before_24_hour_time = $checkin - 86400;
            $current_time = time() + env('TIMEZONE');
            
            if($current_time >= $before_24_hour_time){
                echo json_encode(array('status'=>'error','message'=>'You can cancel booking before 24 hrs only')); die;
            }else{
                $amount = $trans_data->amount;
                $referenceId = $payudata_data['referenceId'];
    
                $baseurl = env('CASHFREE_LIVE_REFUND_URL');
                $appId = env('CASHFREE_APP_ID_LIVE');
                $secretKey = env('CASHFREE_SECRET_KEY_LIVE');
                if(env('CASHFREE_TEST_MODE')){
                    $baseurl = env('CASHFREE_TEST_REFUND_URL');
                    $appId = env('CASHFREE_APP_ID_TEST');
                    $secretKey = env('CASHFREE_SECRET_KEY_TEST');
                }
    
                //echo $baseurl; die;
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => $baseurl.'api/v1/order/refund',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('appId' => $appId ,
                                            'secretKey' => $secretKey,
                                            'referenceId' => $referenceId,
                                            'refundAmount' => $amount,
                                            'refundNote' => 'Cancelled Room Booking',
                                            'refundAmount' => $amount),
                ));
                $response = curl_exec($curl);
                curl_close($curl);
                $response_decode = json_decode($response, true);
                if($response_decode['status'] != 'ERROR'){
                    $trans_data->status = 'ROOM_CANCELLED';
                    $trans_data->ref_id = $response_decode['refundId'];
                    $trans_data->save();
                    echo json_encode(array('status'=>'success','message'=>'Refund has been intiated.')); die;
                }else{
                    echo json_encode(array('status'=>'error','message'=>'Oops! Something went wrong. Please try again')); die;

                }
            }            
        }
    }
  }
}
