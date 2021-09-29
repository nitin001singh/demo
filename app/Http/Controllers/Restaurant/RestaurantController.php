<?php
namespace App\Http\Controllers\Restaurant;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Restaurant;
use App\Notification;
use App\ItemCategory;
use Illuminate\Support\Str;
use Auth;
use DB;
use Storage;
use App\Mail\SendStatusMail;
use Mail;

Class RestaurantController extends Controller
{
	
	public function getImageURL($imagename){
		return response(Storage::disk('s3')->get('images/'.$imagename))->header('Content-Type','image/jpeg')->header('Content-Type','image/png')->header('Content-Type','image/jpg')->header('Content-Type','image/gif');
	}	

	public function getRestaurant(Request $request)
	{
		$user_id = Auth::user()->id;
		$data = Restaurant::orderBy('id','desc')->get();
		return view('restaurant.index',compact('data'));
	}

	public function getRestaurantForm()
	{
		$catedata = ItemCategory::orderBy('id','desc')->get();
		return view('restaurant.create',compact('catedata'));
	}

	public function createRestaurant(Request $request)
	{
/*		echo '<pre>';
		print_r($request->all());
		die;*/
		$extention = '';

		$data = $request->all();
        $validator = Validator::make($data,array(
            'title'                 => 'required',
            'image'                 => 'required',
            // 'description'           => 'required',
            'old_price'           	=> 'required',
            'new_price'          	=> 'required',
            'category_name'         => 'required',
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }else{
			if($request->file('image'))
			{ 
				$image = $request->file('image');
				$s3 = \Storage::disk('s3');

       			$extention = uniqid() .'.'. $image->getClientOriginalExtension();
                $s3filePath = '/images/' . $extention;
                $s3->put($s3filePath, file_get_contents($image), 'public');
				 
				// $extention = time().'.'.$sign->getClientOriginalName();
				// $ext = $request->file('image')->getClientOriginalExtension();
				// if($ext != 'jpeg' && $ext != 'jpg' && $ext != 'pdf' && $ext != 'png' && $ext != 'gif' )
				// {
				// 	return redirect()->back()->with('error', 'Ooops! Only Upload the JPEG, JPG, PNG, PDF File Type.');
				// }

				// $sign->move(public_path().'/images/', $extention);
			}
			$restaurant = new Restaurant();
			$restaurant->title = $request->title;
			$restaurant->slug = Str::slug($request->title);
			$restaurant->description = $request->description;
			$restaurant->old_price = $request->old_price;
			$restaurant->new_price = $request->new_price;
			$restaurant->category_name = $request->category_name;
			$restaurant->address = $request->address;
				/*$restaurant->author_name = $request->author_name;*/
			$restaurant->image = $extention;
			$slug = strtolower($request->title);
			$final_slug = str_replace(" ", "-", $slug);
			$restaurant->slug = $final_slug;
			$restaurant->save();

			$notification = new Notification();
	        $notification->category = "restaurant";
	        $notification->category_id = $restaurant->id;
	        $notification->message = $restaurant->title;
	        $notification->save();
	        

	        $activity = "up_tti";
	        $activity_name = "Rastauratnt";
	        $title = ucwords($restaurant->title);
            $snd_message = 'A New '.$activity_name.' @ The Trade Internationl';
            $this->notificationCheck($activity,$snd_message,$title,$activity_name);


			return redirect('admin/restaurant')->with('sucess', 'Restaurant has successfully created.');
        	
        }
	}

	public function edit($id)
	{
		$catedata = ItemCategory::orderBy('id','desc')->get();
		$data = Restaurant::where('id',$id)->first();
		return view('restaurant.edit',compact('data','catedata'));
	}
	public function update($id,Request $request)
	{
		$data = $request->all();
        $validator = Validator::make($data,array(
            'title'                 => 'required',
            //'image'                 => 'required',
            // 'description'           => 'required',
            'old_price'           	=> 'required',
            'new_price'          	=> 'required',
            'category_name'         => 'required',
          /*  'author_name'           => 'required',*/
            /*'link'                 => 'required',*/
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }
        else{
			$restaurant = Restaurant::where('id',$id)->first();
			$restaurant->title = $request->title;
			$restaurant->slug = $restaurant->slug;
			$restaurant->old_price = $request->old_price;
			$restaurant->new_price = $request->new_price;
			$restaurant->category_name = $request->category_name;
			$restaurant->description = $request->description;
			$restaurant->address = $request->address;
			//$restaurant->author_name = $request->author_name;
			 
			if($request->file('image'))
			{
				$image = $request->file('image');
				$s3 = \Storage::disk('s3');

       			$extention = uniqid() .'.'. $image->getClientOriginalExtension();
                $s3filePath = '/images/' . $extention;
                $s3->put($s3filePath, file_get_contents($image), 'public');
				// $extention = time().'.'.$sign->getClientOriginalName();
				// 	$ext = $request->file('image')->getClientOriginalExtension();
				// 	if($ext != 'jpeg' && $ext != 'jpg' && $ext != 'pdf' && $ext != 'png' && $ext != 'gif' )
				// 	{
				// 		return redirect()->back()->with('error', 'Ooops! Only Upload the JPEG, JPG, PNG, PDF File Type.');
				// 	}

				// 	$sign->move(public_path().'/images/', $extention);
				
				$restaurant->image = $extention;
			}
			$restaurant->save();
           return redirect('admin/restaurant')->with('success','Restaurant has successfully updated');
       }

	}
	public function destroy($id)
	{
			
			$newsdata =  Restaurant::select('image')->where('id',$id)->first();
			//Storage::disk('s3')->delete('images/'.$newsdata->image);
			$data = Restaurant::where('id',$id)->delete();
			if($data != '' || $data != null)
			{
				echo 1;exit;
			}else{

			echo 0;exit;
			}
          return redirect('admin/restaurant')->with('success','Restaurant has successfully deleted');
	}
	public function changeRestaurantStatus($id,$status)
	{
		$change = Restaurant::where('id',$id)->first();
        $change->status = $status;
        $change->save();
        if($change->status != 0){
            return response()->json(['status'=>200]);
        }else{
            return response()->json(['status'=>500]);
        }
	}

}