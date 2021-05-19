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
use App\Models\Provider;
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

    public function updateProvider(Request $request)
    {
        
        $data = DB::connection('website')->select(DB::raw("
            SELECT * FROM (
                SELECT
                    id AS `code`,
                    title AS `name_provider`,
                    MAX(IF(name = 'providerPhone', value, NULL)) AS `phone`,
                    MAX(IF(name = 'providerEmail', value, NULL)) AS `email`,
                    MAX(IF(name = 'providerWebsite', value, NULL)) AS `website`,
                    MAX(IF(name = 'providerAddress', value, NULL)) AS `address`,
                    MAX(IF(name = 'providerCity', value, NULL)) AS `city`,
                    MAX(IF(name = 'providerDistrict', value, NULL)) AS `district`,
                    MAX(IF(name = 'providerCountry', value, NULL)) AS `country`,
                    MAX(IF(name = 'providerLatitude', value, NULL)) AS `latitude`,
                    MAX(IF(name = 'providerLongitude', value, NULL)) AS `longitude`,
                    MAX(IF(name = 'providerFromDay1', value, NULL)) AS `day_from_1`,
                    MAX(IF(name = 'providerToDay1', value, NULL)) AS `day_to_1`,
                    MAX(IF(name = 'providerFromDay2', value, NULL)) AS `day_from_2`,
                    MAX(IF(name = 'providerToDay2', value, NULL)) AS `day_to_2`,
                    MAX(IF(name = 'providerOpeningHours1', value, NULL)) AS `hour_open_1`,
                    MAX(IF(name = 'providerClosingHours1', value, NULL)) AS `hour_close_1`,
                    MAX(IF(name = 'providerOpeningHours2', value, NULL)) AS `hour_open_2`,
                    MAX(IF(name = 'providerClosingHours2', value, NULL)) AS `hour_close_2`,
                    MAX(IF(name = 'providerEmergencyServices', value, NULL)) AS `emergency_services`,
                    MAX(IF(name = 'providerEmergencyPhone', value, NULL)) AS `emergency_phone`,
                    MAX(IF(name = 'providerDirectBilling', value, NULL)) AS `direct_billing`,
                    MAX(IF(name = 'providerAmount', value, NULL)) AS `amount`,
                    MAX(IF(name = 'MedicalType', value, NULL)) AS `medical_type`,
                    MAX(IF(name = 'providerMedicalServices', value, NULL)) AS `medical_services`,
                    MAX(IF(name = 'providersPriceFrom', value, NULL)) AS `price_from`,
                    MAX(IF(name = 'providersPriceTo', value, NULL)) AS `price_to`,
                    lang
                FROM (
                    SELECT
                        item.id,
                        item.title,
                        fd.name,
                        fd.label,
                        rel.value,
                        SUBSTRING_INDEX(item.language, '-', 1) as lang
                    FROM `gd68j_flexicontent_items_tmp` item
                        JOIN `gd68j_flexicontent_fields_item_relations` rel
                        ON item.id = rel.`item_id`
                        JOIN gd68j_flexicontent_fields fd
                        ON fd.id = rel.field_id
                    WHERE item.access = 1
                    AND item.type_id = 12
                ) A
                GROUP BY id, title
            ) A
        "));
        foreach ($data as $row) {
            Provider::updateOrCreate([
                'code'   => $row->code,
            ],[
                'name'           => $row->name_provider,
                'phone'          => $row->phone,
                'email'          => $row->email,
                'website'        => $row->website,
                'address'        => $row->address,
                'city'           => $row->city,
                'district'       => $row->district,
                'country'        => $row->country,
                'latitude'       => $row->latitude,
                'longitude'      => $row->longitude,
                'day_from_1'     => $row->day_from_1,
                'day_to_1'       => $row->day_to_1,
                'day_from_2'     => $row->day_from_2,
                'day_to_2'       => $row->day_to_2,
                'hour_open_1'    => $row->hour_open_1,
                'hour_close_1'   => $row->hour_close_1,
                'hour_open_2'    => $row->hour_open_2,
                'hour_close_2'   => $row->hour_close_2,
                'emergency_services'        => $row->emergency_services,
                'emergency_phone'           => $row->emergency_phone,
                'direct_billing'            => $row->direct_billing,
                'amount'                    => $row->amount,
                'medical_type'              => $row->medical_type,
                'medical_services'          => $row->medical_services,
                'price_from'                => $row->price_from,
                'price_to'                  => $row->price_to,
                'lang'                      => $row->lang
            ]);
        }
    }

    public function truncate_db_test(Request $request){
        if($request->password == 'admin'){
            $tableNames = ['mobile_claim','mobile_claim_file'];
            foreach ($tableNames as $name) {
                DB::table($name)->truncate();
            }
            $request->session()->flash('status', "setting update success"); 
            return redirect('/admin/setting');
        }
    }
}
