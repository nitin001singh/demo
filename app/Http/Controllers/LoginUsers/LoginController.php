<?php

namespace App\Http\Controllers\LoginUsers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Country;
use App\State;
use App\City;
use App\User;
use App\NotificationPreferece;
use App\UserAddress;
use App\Wallet;
use Auth;
use Illuminate\Support\Str;
use App\Mail\ResetPassword;
use App\Mail\NotificationMail;
use Mail;
use DB;

class LoginController extends Controller
{
    
    public function getRegisterForm()
    {
        $country = Country::all();
        return view('auth.register-user',['country'=>$country]);
    }
    public function getAllStates($country_id){
        $state = State::where('country_id',$country_id)->get();
        return response()->json($state);
    }
    public function getAllCities($state_id){
       $cities = City::where('state_id',$state_id)->get();
       return response()->json($cities);
    }

    public function saveUsers(Request $request){
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
            'password' => 'required',
            'c_password' => 'required',
            'g-recaptcha-response' => 'required'
         ]);

        if($request->use_referral_code && User::where('referral_code',strtolower($request->use_referral_code))->count() == 0){
            return redirect()->back()->with("error",'Referral Code is not valid.');
        }

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
            $insert->use_referral_code = strtolower($request->use_referral_code);

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

            $activity = "friends_join";
            $activity_name = "My friends join TTI";
            $title = $referal_user->first_name.' '.$referal_user->last_name.'Use Referral Code By '.$insert->first_name.' '.$insert->last_name;
            $snd_message = $activity_name.' @ The Trade Internationl';
            $this->notificationCheck($activity,$snd_message,$title,$activity_name);
        }
        $insert->save();

        return redirect('login-user')->with('success','Succesfully Registered! Now you have login to continue.');
        
    }

    public function checkValidReferralCode($referral_code){
        $referral_code = strtolower($referral_code);
        if(User::where('referral_code',$referral_code)->count() > 0){
            echo 'valid';die;
        }else{
            echo 'not valid';die;
        }
    }

    public function updateProfile(Request $request)
    {
       // dd($request->all());
        $user = User::where('id',Auth::user()->id)->first();
        if($request->ajax()){
            if($request->hidden_field == "cover-profile"){
                $user->cover_image  = $request->cover_image;
                $user->save();
                return response()->json(["success"=>200]);
            }

            if($request->hidden_field == "profile"){
             $data = $request->file;
             $pos  = strpos($data, ';');
             $type = explode(':', substr($data, 0, $pos))[1];
             $final_type = substr($type, strrpos($type, '/') + 1);
                
             list($type, $data) = explode(';', $data);
             list(, $data)      = explode(',', $data);
             $data = base64_decode($data);
             $image_name= time().'.'.$final_type;

             $path = 'upload/' . $image_name;
             file_put_contents($path, $data);
             $user->image = $image_name;
             $user->save();
             return response()->json(["image"=>$user->image]);
            }
        }
        else{
            $name = explode(' ',$request->full_name);
            $user->salutation = $user->salutation;
            $user->first_name =$name[0];
            $user->last_name = $name[1];
            if($request->self_intro)
                $user->self_intro = $request->self_intro;
            else
                $user->self_intro = $user->self_intro;

            if($request->website)
                $user->website = $request->website;
            else
                $user->website = $user->website;

            if($request->address){
                $user->address = $request->address;

                $address = new UserAddress();
                $address->address = $request->address;
                $address->user_id = $user->id;
                $address->category = "home";
                $address->save();
            }
            else{
                $user->address = $user->address;
            } 

            if($request->language)
                $user->language = $request->language;

            if($request->facebook_link)
                $user->facebook_link = $request->facebook_link;

            if($request->twitter_link)
                $user->twitter_link = $request->twitter_link;

            if($request->insta_link)
                $user->insta_link = $request->insta_link;
 
            $user->phone_number = '+91'.$request->phone;
            $user->email = $user->email;
            $user->gender = $user->gender;
            $user->country = $user->country;
            $user->state = $user->state;

            if($request->city)
                $user->city = $request->city;
            else
                $user->city = $user->city;

            $user->email_verifid_token = $user->email_verifid_token;

            if($request->password)
                $user->password = bcrypt($request->password);
            else
                $user->password = $user->password;

            $user->save();
            return redirect('setting')->with('success','Succesfully Update.');
        }

    }

    public function updatePassword(Request $request){
           
        $user = User::where('id',Auth::user()->id)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        return response()->json(['status' => 200, 'success'=>'Succesfully Update your Password.']);
    }

    public function login(Request $request)
    {

        if($request->email == null){
           $credentials = array(
            'phone_number'=> '+91'.$request->phone,
            'password' => $request->password
            );
        }
        if($request->phone == null){
            $credentials = array(
            'email'=>$request->email,
            'password' => $request->password
            );
        }
        $user_data = ($request->email != null) ? User::where('email',$request->email)->count() : User::where('phone_number','+91'.$request->phone)->count();
        
        if($user_data == 0){
            return redirect('login-user')->with('error','Credential is not Exists to proceed, Please try with the another way .');
        }
        if(!$credentials){
            return redirect('login-user')->with('error','Something Went Wrong.');
        }

        if(Auth::attempt($credentials)){
             if(Auth::user()->role_id == 0 && Auth::user()->is_delete == 0){
                return redirect('users/'.Auth::user()->username.'/review');
                //return redirect('users/review');
             }else{
                return redirect('login-user')->with('error','Account is not exists to this Credentials, Please register to  continue.');
            }
        }
        else{
            
            return redirect('login-user')->with('error','Account is not exists to this Credentials, Please register to  continue.');
       }
    }

    public function logout()
    {
        if(Auth::check()){
            $role = Auth::user()->role_id;
            Auth::logout();
            return $role == 1 ? redirect('admin') : redirect('login-user');
        }
        
    }

    public function sendResetPasswordLink(Request $request)
    {  

       if(User::where('email',$request->email)->count() > 0){
            $user = User::where('email',$request->email)->first();
            Mail::to($request->email)->send(new ResetPassword($user));
            return redirect()->back()->with('success','Thank you! your reset password link is send on your email address please check and proceed.');
       }else{
         return redirect()->back()->with('error','Email Id is not exists.');
       }
    }
    public function forgotPassword(Request $request)
    {
        $user = User::where('email_verifid_token',$request->token)->first();
        if($user){
            return view('change-password',compact('user'));
        }else{
            return redirect('login-user')->with('error','You are unauthorised user.');
        }
    }
    public function savePassword(Request $request)
    {
            $user = User::where('email',$request->email)->first();
            $user->password = bcrypt($request->password);
            $user->save();
            return redirect('login-user')->with('success','Succesfully Update your password.');
    }

}
