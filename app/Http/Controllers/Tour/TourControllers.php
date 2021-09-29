<?php

namespace App\Http\Controllers\Tour;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Tour;
use Illuminate\Support\Str;
use Storage;
class TourControllers extends Controller
{
     public function index()
    {
       $tour = Tour::get();
       return view('tour.index',compact('tour'));
    }

    public function create()
    {
       return view('tour.create');
    }

    public function store(Request $request)
    {
       $validate = $request->validate([
        'title'=>'required',
        'caption'=>'required',
        'description'=>'required',
        'image'=>'required',
        'price'=>'required',
       ]);

       $tour = new Tour();
       $tour->title = $request->title;
       $tour->caption = $request->caption;
       $tour->description = $request->description;
       $tour->price = $request->price;
       $tour->category = $request->category;
       $tour->slug = Str::slug($request->title);
       if($request->image){
           $image = $request->file('image');
           $s3 = \Storage::disk('s3');

            $extention = uniqid() .'.'. $image->getClientOriginalExtension();
            $s3filePath = '/images/' . $extention;
            $s3->put($s3filePath, file_get_contents($image), 'public');
            $tour->image = $extention;
               // $input['imagename'] =time().rand(00,99).$image->getClientOriginalName();
               // $myImg  = $input['imagename'];
               // $myImg  = str_replace(' ', '', $myImg);
               
               // $destinationPath = public_path('/upload/');
               // if($image->move($destinationPath, $input['imagename']))
               // {
               //   $extension = $image->getClientOriginalExtension();
               //   $tour->image = $myImg;
               //  }
       }
       $tour->save();

       return redirect('admin/tours')->with('success','Succesfully Added Tour.');
    }
    public function show($id){
        $tour = Tour::where('id',$id)->first();
        return view('tour.view',compact('tour'));
    }

    public function edit($id)
    {
        $tour = Tour::where('id',$id)->first();
        return view('tour.edit',compact('tour'));
    }

    public function update(Request $request, $id)
    {
       $validate = $request->validate([
       'title'=>'required',
        'caption'=>'required',
        'description'=>'required',
        'price'=>'required',
       ]);

       $tour =  Tour::where('id',$id)->first();
       $tour->title = $request->title;
       $tour->caption = $request->caption;
       $tour->description = $request->description;
       $tour->price = $request->price;
       $tour->category = $request->category;
       $tour->slug = $tour->slug;
       if($request->image){
           $image = $request->file('image');
           $s3 = \Storage::disk('s3');

            $extention = uniqid() .'.'. $image->getClientOriginalExtension();
            $s3filePath = '/images/' . $extention;
            $s3->put($s3filePath, file_get_contents($image), 'public');
            $tour->image = $extention;
               // $input['imagename'] =time().rand(00,99).$image->getClientOriginalName();
               // $myImg  = $input['imagename'];
               // $myImg  = str_replace(' ', '', $myImg);
               
               // $destinationPath = public_path('/upload/');
               // if($image->move($destinationPath, $input['imagename']))
               // {
               //   $extension = $image->getClientOriginalExtension();
               //   $tour->image = $myImg;
               //  }
       }
       else{
         $tour->image = $tour->image;
        }
       $tour->save();

       return redirect('admin/tours')->with('success','Succesfully Updated Tour.');
    }

    public function destroy($id)
    {
        Tour::where('id',$id)->delete();
        return response()->json(['success'=>200]);
    }
}
