<?php
namespace App\Http\Controllers\CMSPage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CMSPage;
use Auth;

Class CMSPageController extends Controller
{
	public function getCMSPage(Request $request)
	{

		$user_id = Auth::user()->id;
		$data = CMSPage::orderBy('id','desc')->get();
	 
		return view('cms-page.index',compact('data'));
	}

	public function getCMSPageForm()
	{
		return view('cms-page.create');
	}

	public function createCMSPage(Request $request)
	{
		$data = $request->all();
        $validator = Validator::make($data,array(
			'title'                 => 'required',
			'description'                 => 'required',
			'meta_title'                 => 'required',
			'meta_description'                 => 'required',
			'meta_keywords'                 => 'required',
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }
        else{
        	$news = new CMSPage();
			$news->title = $request->title;
			$slug = strtolower($request->title);
			$final_slug = str_replace(" ", "-", $slug);
			$news->slug = $final_slug;
			$news->description = $request->description;
			$news->meta_title = $request->meta_title;
			$news->meta_description = $request->meta_description;
			$news->meta_keywords = $request->meta_keywords;
			$news->save();
			return redirect('admin/cms-pages')->with('sucess', 'CMS Page successfully created.');
        }
	}

	public function edit($id)
	{
		$data = CMSPage::where('id',$id)->first();
		return view('cms-page.edit',compact('data'));
	}
	public function update($id,Request $request)
	{
		$data = $request->all();
        $validator = Validator::make($data,array(
            'title'                 => 'required',
            'description'           => 'required',
            'meta_title'            => 'required',
            'meta_description'      => 'required',
            'meta_keywords'         => 'required',
        ));
        if ($validator->fails()) {
            return redirect()->back();
        }
        else{
			$news = CMSPage::where('id',$id)->first();
			$news->title = $request->title;
			$slug = strtolower($request->title);
			$final_slug = str_replace(" ", "-", $slug);
			$news->slug = $final_slug;
			$news->description = $request->description;
			$news->meta_title = $request->meta_title;
			$news->meta_description = $request->meta_description;
			$news->meta_keywords = $request->meta_keywords;
			$news->save();
           return redirect('admin/cms-pages')->with('success','Successfully Updated CMS Page');
       }

	}
}