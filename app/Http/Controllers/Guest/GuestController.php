<?php
namespace App\Http\Controllers\Guest;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Guest;
use Auth;
use DB;
use Storage;

Class GuestController extends Controller
{
	
	public function getImageURL($imagename){
		return response(Storage::disk('s3')->get('images/'.$imagename))->header('Content-Type','image/jpeg')->header('Content-Type','image/png')->header('Content-Type','image/jpg')->header('Content-Type','image/gif');
	}	

	public function getGuest(Request $request)
	{
		$user_id = Auth::user()->id;
		$data = Guest::orderBy('id','desc')->get();
		return view('guest.index',compact('data'));
	}

	public function getGuestForm()
	{
		return view('guest.create');
	}

	public function createGuest(Request $request)
	{
		$extention = '';

		$data = $request->all();
        $validator = Validator::make($data,array(
            'title'                 => 'required',
            'image'                 => 'required',
            'author_name'                 => 'required',
            'description'                 => 'required',
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }else{
			if($request->file('image'))
			{
				/*$file = $request->file('image');
	            $name =time().$file->getClientOriginalName();
	            $filePath = 'images/' . $name;*/
	            //$file = $request->file('image');             
	          /*  $s3 = \Storage::disk('s3');
	            $extention = uniqid() .'.'. $file->getClientOriginalExtension();
	            $s3filePath = '/images/' . $extention;
	            $path = $s3->put($s3filePath, file_get_contents($file), 'public');*/

	            /*$imageName = Storage::disk('s3')->url($s3filePath);
	            print_r($imageName); die;*/
	           // Storage::disk('s3')->put($filePath, file_get_contents($file));

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

				// $sign->move(public_path().'/upload/', $extention);
				//$client->request_sign = $extention;
			}
			$guest = new Guest();
			$guest->title = $request->title;
			$guest->description = $request->description;
			$guest->author_name = $request->author_name;
			$guest->image = $extention;
			$slug = strtolower($request->title);
			$final_slug = str_replace(" ", "-", $slug);
			$guest->slug = $final_slug;
			$guest->save();
			return redirect('admin/guest')->with('sucess', 'Guest has successfully created.');
        	
        }
	}

	public function edit($id)
	{
		$data = Guest::where('id',$id)->first();
		return view('guest.edit',compact('data'));
	}
	public function update($id,Request $request)
	{
		$data = $request->all();
        $validator = Validator::make($data,array(
            'title'                 => 'required',
            'author_name'           => 'required',
            /*'link'                 => 'required',*/
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }
        else{
			$guest = Guest::where('id',$id)->first();
			$guest->title = $request->title;
			$guest->description = $request->description;
			$guest->link = $request->link;
			$guest->author_name = $request->author_name;
			$slug = strtolower($request->title);
			$final_slug = str_replace(" ", "-", $slug);
			$guest->slug = $final_slug;

			if($request->file('image'))
			{
			 
				$image = $request->file('image');
				$s3 = \Storage::disk('s3');

       			$extention = uniqid() .'.'. $image->getClientOriginalExtension();
                $s3filePath = '/images/' . $extention;
                $s3->put($s3filePath, file_get_contents($image), 'public');
				$guest->image = $extention;
			}
			$guest->save();
           return redirect('admin/guest')->with('success','Guest has successfully updated');
       }

	}
	public function destroy($id)
	{
			
			$newsdata =  Guest::select('image')->where('id',$id)->first();
			//Storage::disk('s3')->delete('images/'.$newsdata->image);
			$data = Guest::where('id',$id)->delete();
			if($data != '' || $data != null)
			{
				echo 1;exit;
			}else{

			echo 0;exit;
			}
          return redirect('admin/guest')->with('success','Guest has successfully deleted');
	}

}