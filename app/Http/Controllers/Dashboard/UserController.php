<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\User;

class UserController extends Controller
{
   public function getProfileForm()
   {
       return view('dashboard.update-profile');
   }
   public function saveUpdateData(Request $request){
     // dd($request->all());
    $validate = $request->validate([
      'first_name'=>'required',
      'last_name'=>'required',
      'email'=>'required',
    ]);

    $data = User::where('id',Auth::user()->id)->first();
    $data->first_name = $request->first_name;
    $data->last_name = $request->last_name;
    $data->email = $request->email;

    $data->phone_number = ($request->phone != null) ? '+91'.$request->phone : null;
    $data->password = ($request->password != null) ? bcrypt($request->password) : $data->password;

    if($request->image){
       $image = $request->file('image');
       $input['imagename'] =time().rand(00,99).$image->getClientOriginalName();
       $myImg  = $input['imagename'];
       $myImg  = str_replace(' ', '', $myImg);
       
       $destinationPath = public_path('/upload/');
       if($image->move($destinationPath, $input['imagename']))
       {
         
         $extension = $image->getClientOriginalExtension();
         $data->image = $myImg;
       }
    }
    $data->save();
    return redirect()->back()->with('success','Successfully Updated.');
   }

   public function removeProfileImage(){

      $image = User::where('id',Auth::user()->id)->first();
      $image->image = null;
      $image->save();
      return response()->json(['success'=>200]);
   }
}
