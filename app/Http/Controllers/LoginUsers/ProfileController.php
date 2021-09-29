<?php

namespace App\Http\Controllers\LoginUsers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\City;
use App\BlogPost;
use App\OrderFavourite;
use App\OrderHistory;
use App\Notification;
use App\NotificationPreferece;
use App\UserAddress;
use App\Reservation;
use App\Follow;
use App\RecentViewed;
use App\Rating;
use App\Review;
use App\Wallet;
use App\Bookmark;
use App\Transaction;
use App\Setting;
use App\RoomServiceCategory;
use App\Promocode;
use App\SocialShare;
use Auth;
use Carbon\Carbon;
use View;
use Illuminate\Support\Facades\Route;


class ProfileController extends Controller
{
    public function getauthuser(){
        $allUsers = User::where('id','!=',Auth::user()->id)->where('role_id','!=',1)->orderBy('id','desc')->get();
        $user = User::with('review','follow','bookmark','bookmark.rastaurant','recentviewed','recentviewed.rastaurant','notipref')->where('id',Auth::user()->id)->first();
        return [$user,$allUsers];
    }
    // public function countPhotos($user_id){
    //     $photos = Bookmark::with('rastaurant')->where('user_id',$user_id)->get();
    //     $images = [];
    //     foreach($photos as $key => $value){
    //         if($value->rastaurant){
    //             array_push($images, $value->rastaurant->image);
    //         }
    //     }
    //     return count($images);
    // }

    //  public function countReviews($user_id){
    //     $reviews = Review::where('user_id',$user_id)->get();
    //     return count($reviews);
    // }

    public function index()
    {
        // $data = $this->getauthuser();
        // $user = $data[0];
        // $allUser = $data[1];   
        return view('login-users.profile');
    }
    public function setting()
    {
        $data = $this->getauthuser();
        $user = $data[0];
        // $allUser = $data[1];
        $getCity = City::where('state_id',$user->state)->get();
        return view('login-users.setting',compact('getCity'));
    }
    public function blogPost()
    {
        // $data = $this->getauthuser();
        // $user = $data[0];
        // $allUser = $data[1];
        $blog_post = BlogPost::paginate(15);
        //$total_photos = $this->countPhotos($user->id);
       // $total_reviews = $this->countReviews($user->id);
    return view('login-users.blog-post',compact('blog_post'/*,'user','allUser'*//*,'total_photos','total_reviews'*/));
}
public function blogPostDetails($slug)
{
       /* $data = $this->getauthuser();
        $user = $data[0];
        $allUser = $data[1];*/
        $blog_post = BlogPost::where('slug',$slug)->first();
        $related_blog_post = BlogPost::where('slug','!=',$slug)->get();

        return view('login-users.blog-post-detail',compact('blog_post',/*'user',*/'related_blog_post'));
    }
    public function addAddress(){
        // $data = $this->getauthuser();
        // $user = $data[0];
        // $allUser = $data[1];
        //$total_photos = $this->countPhotos($user->id);
        //$total_reviews = $this->countReviews($user->id);
    return view('login-users.add-address',compact('user'/*,'total_photos','total_reviews'*/));
}
public function MyAddress(){
    $data = $this->getauthuser();
    $user = $data[0];
        // $allUser = $data[1];
    $address = UserAddress::where('user_id',$user->id)->get();
        //$total_photos = $this->countPhotos($user->id);
       // $total_reviews = $this->countReviews($user->id);
return view('login-users.my-addresses',compact(/*'user',*/'address'/*,'allUser'*//*,'total_photos','total_reviews'*/));
}
public function saveUserAddress(Request $request){
    $address = new UserAddress();
    $address->address = $request->address;
    $address->user_id = Auth::user()->id;
    $address->category = $request->category;
    $address->to_address = $request->complete_address;
    if($request->floor_add)
        $address->floor =  $request->floor_add;
    if($request->how_reach)
        $address->how_reach =  $request->how_reach;

    $address->save();
    return redirect('my-address')->with('success','Succcefully Added New Address.');
}
public function updateUserAddress(Request $request,$id){
    $address = UserAddress::where('id',$id)->first();
    $address->address = $request->address;
    $address->user_id = Auth::user()->id;
    $address->category = $request->category;
    $address->to_address = $request->complete_address;
    if($request->floor_add)
        $address->floor =  $request->floor_add;
    if($request->how_reach)
        $address->how_reach =  $request->how_reach;

    $address->save();
    return redirect('my-address')->with('success','Succcefully Updated your Address.');
}

public function changePreferenceStatus($is_email,$is_phone,$activity,$title){

    $old_prference = NotificationPreferece::where('activity',$activity)->where('user_id',Auth::user()->id)->first();
    
    if($old_prference)
    {
       $prference = NotificationPreferece::where('activity',$activity)->where('user_id',Auth::user()->id)->first();
   }else{
      $prference = new NotificationPreferece();
  }

  $prference->user_id = Auth::user()->id;
  $prference->activity = $activity;
  $prference->title = $title;
  $prference->is_email = ($is_email == "true") ? 'Yes':'No';
  $prference->is_phone = ($is_phone == "true") ? 'Yes':'No';
  if($is_email == "true" || $is_phone == "true"){
    $prference->is_active = 'Yes';
}else{
    $prference->is_active = 'No';
}
$prference->save();
return response()->json(['success'=>200]);
}


public function changEmilAddress($email){
    $user = user::where('id',Auth::user()->id)->first();
    $user->email = $email;
    $user->save();
    return response()->json(['success'=>200]);
}

public function deleteAddress($id){
    UserAddress::where('id',$id)->delete();
    return redirect()->back();
}

public function deleteUserAccount($id){
    $user = User::where('id',$id)->first();
    $user->is_delete = 1;
    $user->save();

    Auth::logout();
    return redirect('login-user');
}

public function followUser($id){

       // dd($id,Follow::where('follow_user_id',$id)->count());
   if(Follow::where('follow_user_id',$id)->where('user_id',Auth::user()->id)->count() == 0 )
   {
    $follow = new Follow();
    $follow->user_id = Auth::user()->id;
    $follow->follow_user_id = $id;
    $follow->status = 1;
    $follow->save();

    $title = '';
    $activity_name = "Follow User";
    $activity = "follow";
    $snd_message = 'has follow you, a new Fan @ The Trade Internationl';
    $this->notificationCheck($activity,$snd_message,$title,$activity_name);

    return response()->json(['success'=>200]);
}else{
    $follow = Follow::where('follow_user_id',$id)->where('user_id',Auth::user()->id)->first();
    $follow->status = 1;
    $follow->save();
}
}
public function unFollowUser($id){

    $follow = Follow::where('follow_user_id',$id)->where('user_id',Auth::user()->id)->first();
    $follow->status = 0;
    $follow->save();
    return response()->json(['success'=>200]);
}

public function OrderFavourite()
{
   $order_fav = OrderFavourite::where('user_id',Auth::user()->id)->pluck('order_id');
   if(!empty($order_fav)){
    $data = $this->getauthuser();
    $user = $data[0];
    $order_history = Transaction::where('user_id',$user->id)->whereIn('id',$order_fav)->orderby('id','DESC')->paginate(10);  
    if(!empty($order_history)){
        foreach($order_history as $value){
            $address_id = !empty($value['checkout_form_data']) ? json_decode($value['checkout_form_data'], true)['selectedaddress'] : 0;
            $deliver_address = UserAddress::select('address')->where('id',$address_id)->first();
        }

        $from_address = Setting::where('key','address')->first();
        return view('login-users.favourite',compact('user_data','from_address','order_history','deliver_address'));
    }
}else{
   $user_data = array();
   $from_address = array();
   $order_history = array();
   $deliver_address = array();
   return view('login-users.favourite',compact('user_data','from_address','order_history','deliver_address'));
}
}
public function getItemOrderHistory(){

    $data = $this->getauthuser();
    $user = $data[0];
    $order_history = Transaction::where('user_id',$user->id)->where('txn_type','ITEM')->orderby('id','DESC')->paginate(10);  
    if(!empty($order_history)){
        foreach($order_history as $value){
                //dd($value['checkout_form_data']);
            if(isset($value['checkout_form_data']['selectedaddress']) != null){
                $address_id = !empty($value['checkout_form_data']) ? json_decode($value['checkout_form_data'], true)['selectedaddress'] : 0;
                $deliver_address = UserAddress::select('address')->where('id',$address_id)->first();
            }
        }

        $from_address = Setting::where('key','address')->first();
        return view('login-users.order-history',compact('user_data','from_address','order_history','deliver_address'));
    }
}
public function getRoomOrderHistory(){

    $data = $this->getauthuser();
    $user = $data[0];
    $room_order_history = Transaction::where('user_id',$user->id)->where('txn_type','ROOM')->orderby('id','DESC')->paginate(10); 
    
    return view('login-users.room-order-history',compact('room_order_history'));
       
}
public function getRefundHistory(Request $request){
    
        $baseurl = env('CASHFREE_LIVE_REFUND_URL');
        $appId = env('CASHFREE_APP_ID_LIVE');
        $secretKey = env('CASHFREE_SECRET_KEY_LIVE');
        if(env('CASHFREE_TEST_MODE')){
            $baseurl = env('CASHFREE_TEST_REFUND_URL');
            $appId = env('CASHFREE_APP_ID_TEST');
            $secretKey = env('CASHFREE_SECRET_KEY_TEST');
        }

        $start_date = $request->startDate;
        $end_date = $request->endDate;

        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $baseurl.'api/v1/refunds',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('appId' => $appId ,
                                    'secretKey' => $secretKey,
                                    'startDate' => $start_date,
                                    'endDate' => $end_date),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $response_decode = json_decode($response, true);
        return response()->json(['refund_data'=>$response_decode]);die;

}

public function review($username){
    $allUser = User::where('username','!=',$username)->where('role_id','!=',1)->orderBy('id','desc')->get();
    $user = User::with('review','follow','bookmark','bookmark.rastaurant','recentviewed','recentviewed.rastaurant','notipref')->where('username',$username)->first();
    $category = RoomServiceCategory::get();
    return view('login-users.review',compact('user','allUser','category'));
}

public function recentViewedItems($item_id){
    if(Auth::check() && RecentViewed::where('item_id',$item_id)->where('user_id',Auth::user()->id)->count() == 0){
       $recentview = new RecentViewed();
       $recentview->user_id = Auth::user()->id;
       $recentview->item_id = $item_id;
       $recentview->save();
   }
}

public function notifications()
{
    $notifications = Notification::orderBy('id','desc')->get();
return view('login-users.notifications',compact(/*'user','allUser',*/'notifications'/*,'total_photos','total_reviews'*/));
}

public function photos($username){
    $allUser = User::where('username','!=',$username)->where('role_id','!=',1)->orderBy('id','desc')->limit(10)->get();
    $user = User::with('review','follow','bookmark','bookmark.rastaurant','recentviewed','recentviewed.rastaurant','notipref')->where('username',$username)->first();
    $photos = Bookmark::with('rastaurant')->where('user_id',$user->id)->get();
    $images = [];
    foreach($photos as $key => $value){
        if($value->rastaurant){
            array_push($images, $value->rastaurant->image);
        }
    }

    $total_photos = count($images);
    
    return view('login-users.photos',compact('images','total_photos','user','allUser'));
} 

public function followers($username){
    $allUser = User::where('username','!=',$username)->where('role_id','!=',1)->orderBy('id','desc')->get();
    $user = User::with('review','follow','bookmark','bookmark.rastaurant','recentviewed','recentviewed.rastaurant','notipref')->where('username',$username)->first();

    $followers = Follow::with('followuser')->where('follow_user_id',$user->id)->where('status',1)->get();
    $following = Follow::with('followinguser')->where('user_id',$user->id)->where('status',1)->get();
    
       // dd($followers,$following);
    return view('login-users.followers',compact('followers','following','user','allUser'));
} 
public function recentViewed($username){
    $allUser = User::where('username','!=',$username)->where('role_id','!=',1)->orderBy('id','desc')->get();
    $user = User::with('review','follow','bookmark','bookmark.rastaurant','recentviewed','recentviewed.rastaurant','notipref')->where('username',$username)->first();

        //$total_photos = $this->countPhotos($user->id);
        //$total_reviews = $this->countReviews($user->id);
        // /$avg_rating = 0;
        //$count = 0;
        // /$total_peoples = 0;
    if($user->recentviewed){
        foreach($user->recentviewed as $key => $value){
            $rating = Review::where('item_id',$value->item_id)->where('item_type','restaurant')->sum('rating');
            $count = Review::where('item_id',$value->item_id)->where('item_type','restaurant')->count();
            
            $value['total_peoples'] = $count;
            if($count != 0 && $rating != 0)
                $value['avg_rating'] = $rating / $count;
            else
                $value['avg_rating'] = 0;
            
        }
    }
    
    return view('login-users.recent-viewd',compact('user','allUser'));
} 
public function manageCards(){
        /*$data = $this->getauthuser();
        $user = $data[0];
        $allUser = $data[1];*/
        //$total_photos = $this->countPhotos($user->id);
       // $total_reviews = $this->countReviews($user->id);
        return view('login-users.manage-cards');
    }
    public function refsEarn(){
       /* $data = $this->getauthuser();
        $user = $data[0];
        $allUser = $data[1];*/
       // $total_photos = $this->countPhotos($user->id);
       // $total_reviews = $this->countReviews($user->id);
        return view('login-users.refer-earn');
    }

    public function manageWallets(){
        /*$data = $this->getauthuser();
        $user = $data[0];
        $allUser = $data[1];*/
       // $total_photos = $this->countPhotos($user->id);
       // $total_reviews = $this->countReviews($user->id);
        return view('login-users.manage-wallet');
    }
    public function bookmark()
    {
        $data = $this->getauthuser();
        $user = $data[0];
        //$allUser = $data[1];

        if($user->bookmark){
            foreach($user->bookmark as $key => $value){
                $rating = Review::where('item_id',$value->item_id)->where('item_type','restaurant')->sum('rating');
                $count = Review::where('item_id',$value->item_id)->where('item_type','restaurant')->count();
                
                $value['total_peoples'] = $count;
                if($count != 0 && $rating != 0)
                    $value['avg_rating'] = $rating / $count;
                else
                    $value['avg_rating'] = 0;
                
            }
        }


       // $total_photos = $this->countPhotos($user->id);
        //$total_reviews = $this->countReviews($user->id);
        return view('login-users.bookmark',compact('user'));
    }

    public function TTICredits(){
        $data = $this->getauthuser();
        $user = $data[0];
        
        $wallet = Wallet::where('user_id',$user->id)->orderby('id','DESC')->get();
        // $total_wallet = Wallet::where('user_id',$user->id)->whereIn('txn_type',array('CREDIT','DEBIT'))->sum('amount');       
        // $recharge_wallet = Wallet::where('user_id',$user->id)->whereNotIn('txn_type',array('CREDIT','DEBIT'))->sum('amount');
        
        return view('login-users.tti-credits',compact('wallet')); //,'total_wallet','recharge_wallet'
    }
    public function yourBooking()
    {
        /*$data = $this->getauthuser();
        $user = $data[0];
        $allUser = $data[1];*/
        
        $past_reservation = Reservation::with('user')->where('user_id','!=',0)->where('user_id',Auth::user()->id)->whereDate('date','<', date('Y-m-d'))->get();
        $upcoming_reservation = Reservation::with('user')->where('user_id','!=',0)->where('user_id',Auth::user()->id)->where('date','>=',date('Y-m-d'))->get();

      //  $total_photos = $this->countPhotos($user->id);
       // $total_reviews = $this->countReviews($user->id);

    return view('login-users.your-book',compact(/*'user','allUser',*/'past_reservation','upcoming_reservation'/*,'total_photos','total_reviews'*/));
}

public function saveReviews(Request $request){
    $data = $this->getauthuser();
    $user = $data[0];
    $review = new Review();
    $review->user_id = $user->id;

    if($request->item_type == 'room'){

     $review->category = $request->category;
     $image_arr = [];
     if($request->fileUpload){
        $image = $request->file('fileUpload');
        $s3 = \Storage::disk('s3');
        foreach($image as $file){
          $extention = uniqid() .'.'. $file->getClientOriginalExtension();
          $s3filePath = '/images/' . $extention;
          $s3->put($s3filePath, file_get_contents($file), 'public');
          array_push($image_arr,$extention);
      }

      $review->image = json_encode($image_arr);
  }
}

if($request->likecomment)
    $review->liked = $request->likecomment;

if($request->unlikecomment)
    $review->unliked = $request->unlikecomment;


if($request->message)
    $review->review = $request->message;

$review->item_id = $request->item_id;
$review->item_type = $request->item_type;
$review->rating = $request->rating;
$review->save();  

if($request->ajax()){
    return response()->json(['success'=>200]);
}else{
    return redirect()->back()->with('success','Review Added by '.$user->first_name.' '.$user->last_name);
}
}
public function updateReviews(Request $request,$id){
        //dd($request->all(),$id);

    $data = $this->getauthuser();
    $user = $data[0];
    $review = Review::where('id',$id)->first();
    $review->user_id = $user->id;
    $review->liked = $request->likecomment;
           // $review->unliked = $request->unlikecomment;
    $review->review = $request->message;
    $review->item_id = $review->item_id;
    $review->item_type = $review->item_type;
    $review->rating = $request->rating;
    $review->category = $request->category;
    
    $review->save();  
    return redirect()->back()->with('success','Review Updated by '.$user->first_name.' '.$user->last_name);
}
public function deleteReview($id){
    Review::where('id',$id)->delete();
    return response()->json(['success'=>200]);
}
    // public function rating($rating,$item_id,$item_type){
    //     $data = $this->getauthuser();
    //     $user = $data[0];
    //     if(Rating::where('user_id',$user->id)->where('item_id',$item_id)->where('item_type',$item_type)->count() > 0)
    //     {
    //         $rat = Rating::where('user_id',$user->id)->where('item_id',$item_id)->where('item_type',$item_type)->first();
    //     }else{
    //         $rat = new Rating();
    //     }
    //     $rat->user_id = $user->id;
    //     $rat->rating = $rating;
    //     $rat->item_id = $item_id;
    //     $rat->item_type = $item_type;
    //     $rat->save();
    //     return response()->json(['success'=>200]);
    // }

public function cart(){
    $address = UserAddress::where('user_id',Auth::user()->id)->orderby('id','desc')->get();
    $credit_sum_amt = 0;
    $credit_sub_amt = 0;
    $reward_sum_amt = 0;
    $reward_sub_amt = 0;
    $credit_amount = 0;
    $reward_amount = 0;
    
    $recharge_wallet_query = Wallet::where('user_id',Auth::user()->id)->get();
    if(!$recharge_wallet_query->isEmpty()){
        foreach($recharge_wallet_query as $key => $value){
            if($value->amount_type == 'TTI_CREDIT'){
                if($value->txn_type == 'CREDIT'){
                    $credit_sum_amt += $value->amount;
                }else{
                    $credit_sub_amt += $value->amount;     
                }
            }else{
                if($value->txn_type == 'CREDIT'){
                    $reward_sum_amt += $value->amount;
                }else{
                    $reward_sub_amt += $value->amount;     
                }   
            }               
        }
        $credit_amount = $credit_sum_amt - $credit_sub_amt;
        $reward_amount = $reward_sum_amt - $reward_sub_amt;
    }

    $promocodes = Promocode::where('code_type','N')->get();
    return view('login-users.cart',compact('address','credit_amount','reward_amount','promocodes'));
}

public function saveSocialShareData(Request $request){
    if(SocialShare::where('id',Auth::user()->id)->where('type',$request->social_share)->count() != 1){
        $social = new SocialShare();
        $social->user_id = Auth::user()->id;
        $social->url = $request->url;

        if($request->customFile){
           $image = $request->file('customFile');         
               /*$input['imagename'] =time().rand(00,99).$image->getClientOriginalName();
               $myImg  = $input['imagename'];
               $myImg  = str_replace(' ', '', $myImg);
               
               $destinationPath = public_path('/upload/');
               if($image->move($destinationPath, $input['imagename']))
               {
                 $extension = $image->getClientOriginalExtension();
             }*/
             $s3 = \Storage::disk('s3');

             $extention = uniqid() .'.'. $image->getClientOriginalExtension();
             $s3filePath = '/images/' . $extention;
             $s3->put($s3filePath, file_get_contents($image), 'public');

             $social->screenshot = $extention;
         }
         $social->type = $request->social_share;
         $social->save();
         return redirect()->back()->with('success','Your request has submitted.');
     }
 }


 public function socialShare(Request $request){
    $social_share = SocialShare::where('user_id',Auth::user()->id)->get();
    return view('login-users.socialshare',compact('social_share'));
}


}
