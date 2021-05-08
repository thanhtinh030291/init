<?php

namespace App\Http\Controllers;
use App\Models\User;
use Auth;
use App\Models\Setting;
use App\Models\HBS_PCV_PD_PLAN;
use App\Models\PlanHbsConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\Models\MobileUser;
use DB;


class SettingController extends Controller
{
    
    //use Authorizable;
    public function __construct()
    {
        //$this->authorizeResource(Product::class);
        parent::__construct();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $setting = Setting::where('id', 1)->first();
        if($setting === null){
            $setting = Setting::create([]);
        }
        $admin_list = User::getListIncharge();
        return view('settingManagement.index', compact('setting','admin_list'));
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data = $request->except([]);
        Setting::updateOrCreate(['id' => 1], $data);

        $request->session()->flash('status', "setting update success"); 
        return redirect('/admin/setting');
    }

    public function notifiAllUser(Request $request)
    {
        $data = $request->except([]);
        $text_notifi = $request->message;
        $arr_id = User::pluck('id');
        notifi_system($text_notifi, $arr_id);
    }
    
    public function updatePlan(Request $request){
        $HBS_PCV_PD_PLAN = HBS_PCV_PD_PLAN::get();
        foreach ($HBS_PCV_PD_PLAN as $key => $value) {
            $PlanHbsConfig = PlanHbsConfig::updateOrCreate([
                'plan_id'   => $value->ben_head,
                'company' => 'pcv',
            ],[
                'plan_id'     => $value->plan_id,
                'rev_no'     => $value->rev_no,
                'plan_desc'    => $value->plan_desc
            ]);
        }
        $request->session()->flash('status', "setting update success"); 
        return redirect('/admin/setting');
    }
    
    public function updatePass(Request $request){
        $MobileUser = MobileUser::all();
        foreach ($MobileUser as $key => $value) {
            
        }
    }

    
}
