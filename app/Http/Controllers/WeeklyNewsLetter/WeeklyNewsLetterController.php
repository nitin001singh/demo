<?php

namespace App\Http\Controllers\WeeklyNewsLetter;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WeeklyNewsLetter;
use Illuminate\Support\Str;
use App\Notification;
use App\User;
use Auth;
use Storage;

class WeeklyNewsLetterController extends Controller
{ 
    public function index()
    {
        $weeklynewslettr = WeeklyNewsLetter::orderBy('id','desc')->get();
        return view('weeklynewslettr.index',compact('weeklynewslettr'));
    }

    public function create()
    {
       //$user = User::where('id','!=', Auth::user()->id)->get();
       return view('weeklynewslettr.create');
    }

    public function store(Request $request)
    {
       $validate = $request->validate([
        'title'=>'required',
        'author_name'=>'required',
        'description'=>'required',
        'fileUpload'=>'required',
       ]);

       $blog = new WeeklyNewsLetter();
       $blog->user_id = 0; //$request->user;
       $blog->title = $request->title;
       $blog->author_name = $request->author_name;
       $blog->description = $request->description;
       $blog->slug = Str::slug($request->title);
       if($request->fileUpload){
           $image = $request->file('fileUpload');
           $s3 = \Storage::disk('s3');

           $extention = uniqid() .'.'. $image->getClientOriginalExtension();
           $s3filePath = '/images/' . $extention;
           $s3->put($s3filePath, file_get_contents($image), 'public');

           $blog->image = $extention;
       }
       $blog->save();

        $notification = new Notification();
        $notification->category = "weeklynewslettr";
        $notification->category_id = $blog->id;
        $notification->message = $blog->title;
        $notification->save();
        

        $activity = "weeknesltr";
        $activity_name = "WeeklyNewsLetter";
        $title = ucwords($blog->title);
        $snd_message = 'A New '.$activity_name.' @ The Trade Internationl';
        $this->notificationCheck($activity,$snd_message,$title,$activity_name);

       return redirect('admin/weekly-newsletter')->with('success','Succesfully Added WeeklyNewsLetter.');
    }

    public function edit($id)
    {
        $user = User::where('id','!=', Auth::user()->id)->get();
        $WeeklyNewsLetter = WeeklyNewsLetter::where('id',$id)->first();
        return view('weeklynewslettr.edit',compact('WeeklyNewsLetter','user'));
    }

    public function update(Request $request, $id)
    {
       $validate = $request->validate([
        'title'=>'required',
        'author_name'=>'required',
        'description'=>'required',
        //'image'=>'required',
       ]);

       $blog =  WeeklyNewsLetter::where('id',$id)->first();
       $blog->user_id = 0; //$request->user;
       $blog->title = $request->title;
       $blog->author_name = $request->author_name;
       $blog->description = $request->description;
       if($request->fileUpload){
           $image = $request->file('fileUpload');
           $s3 = \Storage::disk('s3');

           $extention = uniqid() .'.'. $image->getClientOriginalExtension();
           $s3filePath = '/images/' . $extention;
           $s3->put($s3filePath, file_get_contents($image), 'public');

           $blog->image = $extention;
        }else{
         $blog->image = $blog->image;
        }
       $blog->save();

       return redirect('admin/weekly-newsletter')->with('success','Succesfully Updated WeeklyNewsLetter.');
    }

    public function destroy($id)
    {
        WeeklyNewsLetter::where('id',$id)->delete();
        return response()->json(['success'=>200]);
    }
}
