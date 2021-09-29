<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Mail;
use App\Mail\SendToAdminMail;
use App\Mail\SendToUserMail;
use App\ContactUs;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class ContactUsControllers extends Controller
{
     public function saveContactDetails(Request $request){

     	 $data = $request->all();
     	 $validator = Validator::make($data, [
			'name'     => 'required',
			'email'    => 'required',
			'phone'    => 'required',
			//'subject'  => 'required',
			'message'  => 'required',
		]);

		if ($validator->fails()) {
			if($request->home_contact_us)
				return redirect('/')->withErrors($validator)->with('error','something went wrong.');
			else
				return redirect('contact-us')->withErrors($validator)->with('error','something went wrong.');

		}else{
	     	$contact = new ContactUs();
	     	$contact->name = ucwords($request->name);
	     	$contact->email = $request->email;
	     	$contact->phone = '+91'.$request->phone;
	     	if($request->subject){
	    			$contact->subject = $request->subject;
	    			$subjectAdmin = "Contact Us Enquiry";
	    			$subjectUser = "Thank you for submitting your query";
	     	}

	     	if($request->booking_purpose){
	     		$contact->booking_purpose = $request->booking_purpose;
	     		$subjectAdmin = $subjectUser = "Thank you for submitting your query";
	     	}
			if($request->company){
				$contact->compny = $request->company;
				$subjectAdmin = $subjectUser = "Thank you for submitting your query";
			}

	     	$contact->message = $request->message;
	     	$contact->services = $request->services;
	     	$contact->save();

	     	Mail::to(env('MAIL_FROM_ADDRESS'))->send(new SendToAdminMail($contact,$subject));
	     	Mail::to($request->email)->send(new SendToUserMail($contact->name,$subjectUser));
	     	return redirect()->back()->with('success',"Your submission is received and we will contact you soon.");
	     }
     }
}
