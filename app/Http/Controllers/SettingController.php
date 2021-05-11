<?php

namespace App\Http\Controllers;
use App\Models\User;
use Auth;
use App\Models\Setting;
use App\Models\HBS_PCV_PD_PLAN;
use App\Models\HBS_BSH_PD_PLAN;
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
    
    public function updatePlan(Request $request)
    {
        $hbs_pd_plans = [
            'HBS_PCV_PD_PLAN' => [
                'company' => 'pcv',
                'is_benefit_ready' => 1
            ],
            'HBS_BSH_PD_PLAN' => [
                'company' => 'bsh',
                'is_benefit_ready' => 0
            ]
        ];
        
        foreach ($hbs_pd_plans as $hbs => $plan) {
            $class = "App\\Models\\{$hbs}";
            $data = $class::get();
            $this->_updateOrCreatePlanHbsConfig($data, $plan['company'], $plan['is_benefit_ready']);
        }
        
        $request->session()->flash('status', "setting update success"); 
        return redirect('/admin/setting');
    }
    
    public function updatePass(Request $request)
    {
        $MobileUser = MobileUser::all();
        foreach ($MobileUser as $key => $value) {
            
        }
    }

    private function _updateOrCreatePlanHbsConfig($data, $company, $is_benefit_ready)
    {
        foreach ($data as $row) {
            $PlanHbsConfig = PlanHbsConfig::updateOrCreate([
                'plan_id'   => $row->plan_id,
                'rev_no'   => $row->rev_no,
                'company' => $company,
            ],[
                'plan_id'           => $row->plan_id,
                'rev_no'            => $row->rev_no,
                'plan_desc'         => $row->plan_desc,
                'is_benefit_ready'  => $is_benefit_ready,
            ]);
        }
    }
}
