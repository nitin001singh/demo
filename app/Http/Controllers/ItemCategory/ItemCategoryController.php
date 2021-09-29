<?php

namespace App\Http\Controllers\ItemCategory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ItemCategory;
use Illuminate\Support\Facades\Validator;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = ItemCategory::orderBy('id','desc')->get();
        return view('itemcategory.index',compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('itemcategory.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data,array(
            'value' => 'required',
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }else{
            $cate = new ItemCategory();
            $cate->cate_name =  $request->value;
            $cate->save();

            return redirect('admin/itemcategory')->with('sucess', 'Category has successfully created.');
        }
    }
 
    public function edit($id)
    {
       //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ItemCategory::where('id',$id)->delete();
        echo 1;die;
    }
}
