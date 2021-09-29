<?php

namespace App\Http\Controllers\DeleteAccounts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class DeleteAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $user = user::with('country_r','state_r','city_r')->where('role_id','!=',1)->where('is_delete',1)->get();
       return view('deleted-accounts.index',compact('user'));
    }
    public function enabledisbaleAccount($id)
    {
        $user = User::where('id',$id)->first();
        $user->is_delete = ($user->is_delete == 0) ? 1 : 0;
        $user->save();
        return redirect()->back()->with('success','Succcessfully Update Status.');
    }
}
