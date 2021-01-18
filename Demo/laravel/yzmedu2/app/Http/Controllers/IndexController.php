<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

class IndexController extends Controller
{
    // 首页-展示列表
    public function index(){
        $list =  DB::table('pur_master_bank_info')->offset(20)->limit(10)->get();


        return view('user')->with('list',$list);

    }

    public function showlist(){
        $list =  DB::table('pur_master_bank_info')->offset(20)->limit(10)->get();


        return view('user')->with('list',$list);

    }
}
