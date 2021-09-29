<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Setting;
use App\User;
use App\NotificationPreferece;
use App\Mail\NotificationMail;
use Mail;
use DB;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function notificationCheck($activity,$snd_message,$title,$activity_name)
    {
        $data = NotificationPreferece::groupBy('user_id')->get();
        
        if(!$data->isEmpty()){
         foreach($data as $key => $usr){
            //dd($usr);
            //foreach($value as $usr){
              //  dd($usr);
                if($activity == $usr->activity && $usr->is_active == 'Yes'){
                    if($title = '')
                        $title = $users_data ->first_name.' '.$users_data->last_name;
                    else
                        $title = $title;

                    $users_data = User::where('id',$usr->user_id)->first();
                    if($usr->is_email == 'Yes'){
                        $subject = 'The Trade Internationl';
                        $message = $title.' '.$snd_message;

                        Mail::to($users_data->email)->send(new NotificationMail($subject,$message,$activity_name));
                    }
                    if($usr->is_phone == 'Yes'){
                        $sms_msg = "Follow Request";
                        $smsmessage = rawurlencode($sms_msg);
                        $phone = "+919928044872";

                        $handle = curl_init();
                        curl_setopt($handle,CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($handle, CURLOPT_URL, 'http://msg.icloudsms.com/rest/services/sendSMS/sendGroupSms?AUTH_KEY=23dca28ef0fdbe12d5d8f6f4c5379a0&message='.$smsmessage.'&senderId=BCIIND&routeId=1&mobileNos='.$phone.'&smsContentType=english&entityid=1201161158267185985&templateid=1207161356749562621');
                        
                        $response = curl_exec($handle);
                        curl_close($handle);
                    }
                }
            //}
         }
        }
    }
}
