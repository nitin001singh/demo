<?php

namespace App\Http\Controllers\SocialShare;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Wallet;
use App\SocialShare;

class SocialShareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $social_share = SocialShare::with('user')->orderBy('id','desc')->get();
       return view('social-share.index',compact('social_share'));
    }

    public function changeSocialStatus($id,$status,$type){

        if($status != ""){
            if($status == "R"){
                SocialShare::where('id',$id)->delete();
                echo "success";die;
            }
            $social_share = SocialShare::where('id',$id)->first();
            $social_share->status = $status;
            $social_share->save();

            if($social_share->status == 'A'){
                $wallet = new Wallet();
                $wallet->user_id = $social_share->user_id;
                if($type == 'Facebook')
                    $wallet->amount = env('FACEBOOK_SOCIAL_MEDIA_SHARE_POINTS');
                if($type == 'Instagram')
                    $wallet->amount = env('INSTA_SOCIAL_MEDIA_SHARE_POINTS');
                if($type == 'Twitter')
                    $wallet->amount = env('TWITTER_SOCIAL_MEDIA_SHARE_POINTS');
                if($type == 'Google')
                    $wallet->amount = env('GOOGLE_SOCIAL_MEDIA_SHARE_POINTS');

                $wallet->message = $type." Social Share Reward Amount";
                $wallet->txn_type = 'credit';
                $wallet->amount_type = 'TTI_REWARD';
                $wallet->save();
            }
            echo "success";die;
        }
    }

    public function destroy($id)
    {
        SocialShare::where('id',$id)->delete();
        echo "success";die;
    }
}
