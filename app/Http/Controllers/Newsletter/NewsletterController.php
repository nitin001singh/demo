<?php
namespace App\Http\Controllers\Newsletter;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Newsletter;
use Auth;
use DB;

Class NewsletterController extends Controller
{
	public function getNewsletter(Request $request){
		$data = DB::select('SELECT * FROM newsletters ORDER BY id DESC ');
		return view('newsletter.index',compact('data'));
	}

	public function exportCSV(Request $request){
		$tasks = DB::select('SELECT * FROM newsletters ORDER BY id DESC');
		if(!empty($tasks)){
			$fileName = 'newsletter-subscribers.csv';			 

	        $headers = array(
	            "Content-type"        => "text/csv",
	            "Content-Disposition" => "attachment; filename=$fileName",
	            "Pragma"              => "no-cache",
	            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
	            "Expires"             => "0"
	        );

	        $columns = array('Email', 'Created');
	        $callback = function() use($tasks, $columns) {
	            $file = fopen('php://output', 'w');
	            fputcsv($file, $columns);

	            foreach ($tasks as $task) {
	                $row['email']  = $task->email;
	                $row['created']  = $task->created_at;
	              
	                fputcsv($file, array($row['email'], $row['created']));
	            }

	            fclose($file);
	        };

	        return response()->stream($callback, 200, $headers);
		}
	}

}