<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $count_claim = \App\Models\MobileClaim::count();
        $count_user_mobile = \App\Models\MobileUser::count();
        
	    //dd ($load[0]);
        return view('home',compact('count_claim','count_user_mobile'));
    }
}
