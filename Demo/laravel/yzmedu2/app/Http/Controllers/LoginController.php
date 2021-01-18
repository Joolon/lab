<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class LoginController extends Controller
{
    //
    public function index(){
        return view('login');
    }


    //
    public function check(){
        dd($_POST);
    }


    public function putWeb(){
        return view('putWeb');
    }


    public function putHandle(Request $request){
        dd($request->input());
    }
}
