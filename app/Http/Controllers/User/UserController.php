<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;
use App\Country;
use App\State;
use App\Wallet;
use App\NotificationPreferece;
use App\City;
use Illuminate\Support\Str;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = User::with('country_r','state_r','city_r')->where('role_id','!=',Auth::user()->role_id)->orderBy('id','desc')->get();
        return view('user.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       $country = Country::get();
       return view('user.create',compact('country'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
       $validate = $request->validate([
            'salutation' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'gender' => 'required',
            'email' => 'required|unique:users',
            'phone_number' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'address' => 'required',
            'password' => 'required',
            'c_password' => 'required',
         ]);

        if($request->use_referral_code && User::where('referral_code',strtolower($request->use_referral_code))->count() == 0){
            return redirect()->back()->with("error",'Referral Code is not valid.');
        }

        $referral_code = ($request->use_referral_code != null) ? strtolower($request->use_referral_code) : null; 
 
        $insert = new User();
        $insert->salutation=$request->salutation;
        $insert->username= strtolower($request->first_name).'-'.strtolower($request->last_name).'-'.random_int(100000,999999);
        $insert->first_name=ucfirst($request->first_name);
        $insert->last_name = ucfirst($request->last_name);
        $insert->gender = $request->gender;
        $insert->email = $request->email;
        $insert->phone_number = '+91'.$request->phone_number;
        $insert->country = $request->country;
        $insert->state = $request->state;
        $insert->city = $request->city;
        $insert->email_verifid_token = Str::random(20);
        $insert->referral_code = strtolower(Str::random(6));
        $insert->use_referral_code = $referral_code;
        $insert->address = $request->address;
        $insert->password = bcrypt($request->password);
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

        if($request->use_referral_code){
            $referal_user = User::where('referral_code',$insert->use_referral_code)->first();

            $referral_wallet = new Wallet();
            $referral_wallet->user_id = $referal_user->id;
            $referral_wallet->amount = env('REFERRAL_SIGNUP_AMOUNT');
            $referral_wallet->message = "Referral Amount";
            $referral_wallet->txn_type = 'credit';
            $referral_wallet->amount_type = 'TTI_REWARD';
            $referral_wallet->save();

                       
            $new_user_record = User::where('id',$insert->id)->first();
            $new_user_record->parent_id = $referal_user->id;
            $new_user_record->save();

            // $activity = "friends_join";
            // $activity_name = "My friends join TTI";
            // $title = $referal_user->first_name.' '.$referal_user->last_name.'Use Referral Code By '.$insert->first_name.' '.$insert->last_name;
            // $snd_message = $activity_name.' @ The Trade Internationl';
            // $this->notificationCheck($activity,$snd_message,$title,$activity_name);
        }
        
         
        return redirect('admin/user')->with('success','Successfully Added New User.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $data = User::with('country_r','state_r','city_r')->where('id',$id)->where('role_id','!=',Auth::user()->role_id)->first();
        return view('user.view',compact('data'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       $data = User::with('country_r','state_r','city_r')->where('id',$id)->where('role_id','!=',Auth::user()->role_id)->first();
       $country = Country::get();
       $state = State::get();
       $city = City::get();
        return view('user.edit',compact('data','country','state','city'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    { 
        $insert = User::where('id',$id)->first();
        $insert->salutation=$request->salutation;
        $insert->username= strtolower($request->first_name).'-'.strtolower($request->last_name).'-'.random_int(100000,999999);
        $insert->first_name=ucfirst($request->first_name);
        $insert->last_name = ucfirst($request->last_name);
        $insert->gender = $request->gender;
        $insert->email = $request->email;
        $insert->phone_number = '+91'.$request->phone_number;
        $insert->country = $request->country;
        $insert->state = $request->state;
        $insert->city = $request->city;
        $insert->email_verifid_token = $insert->email_verifid_token;
        $insert->referral_code = $insert->referral_code;
        $insert->use_referral_code = $insert->use_referral_code;
        $insert->address = $request->address;
        $insert->password = ($request->password) ? bcrypt($request->password) : $insert->password;
        $insert->save();
        return redirect('admin/user')->with('success','Successfully Updated User.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      User::where('id',$id)->delete();
      return 200;
    }
}
