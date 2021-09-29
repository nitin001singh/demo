<?php
namespace App\Http\Controllers\Contact;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ContactUs;
use App\Contact;
use App\Setting;
use Auth;
use DB;
use Storage;

Class ContactController extends Controller
{
	// public function getContact(Request $request)
	// {
	// 	$user_id = Auth::user()->id;
	// 	$data = ContactUs::orderBy('created_at','asc')->get();
	// 	return view('contact.index',compact('data'));
	// }
	public function getContactUs(Request $request)
	{
		$user_id = Auth::user()->id;
		$data_contact = ContactUs::where('subject','!=',null)->orderBy('created_at','desc')->get();
		return view('contact.index',compact('data_contact'));
	}
	public function getContactBanquet(Request $request)
	{
		$user_id = Auth::user()->id;
		$data_banquet = ContactUs::where('booking_purpose','!=',null)->orderBy('created_at','desc')->get();
		return view('contact.index',compact('data_banquet'));
	}
	public function getContactMeeting(Request $request)
	{
		$user_id = Auth::user()->id;
		$data_meeting = ContactUs::where('compny','!=',null)->orderBy('created_at','desc')->get();
		return view('contact.index',compact('data_meeting'));
	}

	public function show($id){
		$data = ContactUs::where('id',$id)->first();
		return view('contact.view',compact('data'));
	}

	public function destroy($id)
	{
			$data = ContactUs::where('id',$id)->delete();
			if($data != '' || $data != null)
			{
				echo 1;exit;
			}else{

			echo 0;exit;
			}
	}

}