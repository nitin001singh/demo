<?php

namespace App\Http\Controllers\RoomServiceCategory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\RoomServiceCategory;
use App\Review;

class RoomServiceCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $category = RoomServiceCategory::orderBy('id','desc')->get();
       return view('room-review-category.index',compact('category'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       return view('room-review-category.create-edit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $category = new RoomServiceCategory();
        $category->name = ucfirst($request->name);
        $category->save();
        return redirect('admin/room-services-category')->with("success",'Successfully Created Record.');        
    }

    
    public function edit($id)
    {
       $category = RoomServiceCategory::orderBy('id','desc')->first();
       return view('room-review-category.create-edit',compact('category'));
    }
    
    public function update(Request $request, $id)
    {
        $category = RoomServiceCategory::where('id',$id)->first();
        $category->name = ucfirst($request->name);
        $category->save();
        return redirect('admin/room-services-category')->with("success",'Successfully Created Record.');   
    }
    
    public function destroy($id)
    {
       $category = RoomServiceCategory::where('id',$id)->delete();
       echo 200;die;
    }

    public function getRoomReviews(){
      $room_reviews = Review::with('user','room')->where('item_type','room')->get();
      return view('reviews.index',compact('room_reviews'));
    }

    public function viewRoomReviews($id){
      $room_reviews = Review::with('user','room')->where('id',$id)->first();
      return view('reviews.view',compact('room_reviews'));
    }

    public function getRestaurantReviews(){
      $rastro_reviews = Review::with('user')->where('item_type','restaurant')->get();
      return view('reviews.index',compact('rastro_reviews'));
    }

    public function viewrestaurantReviews($id){
      $rastro_reviews = Review::with('user','restaurant')->where('id',$id)->first();
      return view('reviews.view',compact('rastro_reviews'));
    }

    public function sendReply(Request $request){
         $reviews = Review::where('id',$request->id)->first();
         $reviews->reply = $request->message;
         $reviews->save();
         return response()->json(['status'=>'success']);
    }
}
