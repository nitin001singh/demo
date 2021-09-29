<?php
namespace App\Http\Controllers\Promocode;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Promocode;
use Auth;
use DB;
use Storage;

Class PromocodeController extends Controller
{
	
	public function getImageURL($imagename){
		return response(Storage::disk('s3')->get('images/'.$imagename))->header('Content-Type','image/jpeg')->header('Content-Type','image/png')->header('Content-Type','image/jpg')->header('Content-Type','image/gif');
	}	

	public function getPromocode(Request $request)
	{
		$user_id = Auth::user()->id;
		$data = Promocode::orderBy('id','desc')->get();
		return view('promocode.index',compact('data'));
	}

	public function getPromocodeForm()
	{
		return view('promocode.create');
	}

	public function createPromocode(Request $request)
	{
			
		$extention = '';
		$data = $request->all();
		//dd($data);
        $validator = Validator::make($data,array(
            'no_of_promocode' => 'required',
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }else{
			
			//for ($i=0; $i < $request->no_of_promocode; $i++) { 
				$promo = new Promocode();
				$promo->code = $request->no_of_promocode; //$this->generatePromoCode(10);
				$promo->type_of_discount = $request->type_of_discount;
				$promo->discount = $request->discount;
				$promo->max_discount = $request->max_discount;
				$promo->min_order_value = $request->min_order_value;
				$promo->validity_period = $request->validity_period;
				$promo->from_time = $request->from_time_slot;
				$promo->to_time = $request->to_time_slot;
				$promo->type_of_customer = $request->type_of_customer;
				$promo->save();
			//}			
			 
			return redirect('admin/promocode')->with('sucess', 'Promocode has successfully created.');
        }
	}

	function generatePromoCode($str_length) { 
    	$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; 
    	$new_pr_code = substr(str_shuffle($str),0, $str_length);

    	$is_promocode_exist = Promocode::where('code',$new_pr_code)->first();
    	if(!empty($is_promocode_exist)){
    		$this->generatePromoCode(10);
    	}else{
    		return substr(str_shuffle($new_pr_code),0, $str_length); 
    	}   	
	} 

	public function edit($id)
	{
		$data = Promocode::where('id',$id)->first();
		return view('promocode.edit',compact('data'));
	}
	public function destroy($id)
	{
		$data = Promocode::where('id',$id)->delete();
		if($data != '' || $data != null)
		{
			echo 1;exit;
		}else{

		echo 0;exit;
		}
		return redirect('admin/promocode')->with('success','Promocode has successfully deleted');
	}

}