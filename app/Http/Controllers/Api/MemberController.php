<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MobileUser;
use App\Models\HbsMember;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use App\Helps\PcvInsuredCardBuilder;
use App\Helps\PcvBenefitBuilder;

class MemberController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
    }
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'pocy_no' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 201 );       
        }
        
        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->count();
        
        if($HbsMember == 0){
            return $this->sendError(__('frontend.pocy_not_exist') , 201 );       
        }

        $HbsMember = HbsMember::select('mbr_no','company')->where('pocy_no', $request->pocy_no)->where('email', $request->email)->distinct()->get();
        if($HbsMember->count() != 1){
            return $this->sendError('Please use eKYC', 10 );       
        }

        $MobileUser = MobileUser::where('mbr_no', $HbsMember[0]->mbr_no )->orWhere('email', $request->email)->count();
        if ($MobileUser != 0) {
            return $this->sendError(__('frontend.account_exist'), 101 );
        }

        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->where('email', $request->email)->first();
        if($HbsMember->age < config('min_age_use_app')){
            return $this->sendError(__('frontend.member_not_exist'), 101 );
        }
        
        try {
            DB::beginTransaction();
            $password = Str::random(8);
            $fullname = $HbsMember->mbr_first_name ? $HbsMember->mbr_first_name ." " : "";
            $fullname = $HbsMember->mbr_mid_name ? $fullname . $HbsMember->mbr_mid_name . " " : $fullname ; 
            $fullname = $HbsMember->mbr_first_name ? $fullname . $HbsMember->mbr_first_name . " " : $fullname ; 
            $MobileUser = MobileUser::create([
                'pocy_no' => $HbsMember->pocy_no,
                'mbr_no' => $HbsMember->mbr_no,
                'password' => hashpass($password),
                'fullname' => trim($fullname)  ,
                'tel' => $HbsMember->tel,
                'email' => $request->email,
                'is_policy_holder' => $HbsMember->is_policy_holder,
                'language' => "_vi",
                'address' => $HbsMember->address
            ]);
            $data['contents'] = sprintf(__('frontend.create_mobile_user_message'),$fullname,config('app.name'),$request->email, $password);
            sendEmail($MobileUser, $data,'templateEmail.noTeamplate', __('frontend.create_mobile_user_subject'));
            DB::commit();
            return $this->sendResponse($MobileUser , sprintf( __('frontend.register_success'), $request->email) , 0);
        } catch (Exception $e) {
            Log::error(generateLogMsg($e));
            DB::rollback();
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
    }

    /**
     * Register ekyc api
     *
     * @return \Illuminate\Http\Response
     */
    public function ekyc(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'pocy_no' => 'required',
            'photo_front' => 'required',
            'photo_back' => 'required',
            'photo_face' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 201 );       
        }
        $lang = App::isLocale('en') ? 'en' : 'vi';
        
        $fb_id = $request->fb_id;
        $gg_id = $request->gg_id;
        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->count();
        
        if($HbsMember == 0){
            return $this->sendError(__('frontend.pocy_not_exist') , 201 );       
        }

        $body_face_matching = [
            'img1' => $request->photo_front,
            'img2' => $request->photo_face,
        ];

        $body_face_orc = [
            'img1' => $request->photo_front,
            'img2' => $request->photo_back,
        ];
        $headers = [
            'Content-Type' => 'application/json'
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'auth' => [
                config("constants.KEY_API_EKYC"),
                config("constants.SECRET_API_EKYC")
            ]
        ]);
        $mbr_name = null;
        $dob = null; 
        
        try {
            // ocr
            $response_ocr = $client->request("POST", config("constants.URL_API_EKYC")."/api/v2/ekyc/cards?format_type=base64&get_thumb=false" , ['json'=>$body_face_orc]);
            $response_ocr =  json_decode($response_ocr->getBody()->getContents());
            if($response_ocr->errorCode != 0){
                return $this->sendError($response_ocr->errorMessage , $response_ocr->errorCode );
            }else{
                if(isset($response_ocr->data[1])){
                    $mbr_name = strtoupper(vn_to_str($response_ocr->data[1]->info->name));
                    $dob = $response_ocr->data[1]->info->dob;
                }else{
                    return $this->sendError(__("frontend.id_font_requid") , 201 );
                }
            }
            //match_face
            $response_match = $client->request("POST", config("constants.URL_API_EKYC")."/api/v2/ekyc/face_matching?type1=card&format_type=base64&is_thumb=false" , ['json'=>$body_face_matching]);
            $response_match =  json_decode($response_match->getBody()->getContents());
            
            if($response_match->data->invalidCode != 0 && $response_match->data->matching <= 75){
                return $this->sendError(config("mess_match_".$lang.".".$response_match->data->invalidCode) , $response_match->data->invalidCode );
            }elseif($response_match->data->matching <= 75){
                return $this->sendError(__('frontend.ekyc_not_match') , 201 );
            }
            
        } catch (\Throwable $th) {
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
        // change format dob
        if(date_create_from_format('d-m-Y', $dob)){
            $dob =  date_create_from_format('d-m-Y', $dob)->format('Y-m-d');
        }elseif(date_create_from_format('d/m/Y', $dob)){
            $dob =  date_create_from_format('d/m/Y', $dob)->format('Y-m-d');
        }elseif(date_create_from_format('j-M-Y', $dob)){
            $dob =  date_create_from_format('j-M-Y', $dob)->format('Y-m-d');
        }elseif(date_create_from_format('j/M/Y', $dob)){
            $dob =  date_create_from_format('jMY', $dob)->format('Y-m-d');
        }elseif(date_create_from_format('jMY', $dob)){
            $dob =  date_create_from_format('jMY', $dob)->format('Y-m-d');
        }elseif(date_create_from_format('j M Y', $dob)){
            $dob =  date_create_from_format('j M Y', $dob)->format('Y-m-d');
        }elseif(date_create_from_format('d m Y', $dob)){
            $dob = date_create_from_format('d m Y', $dob)->format('Y-m-d');
        }else{
            return $this->sendError(__('frontend.id_font_requid') , 201 );
        }

        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)
                    ->whereRaw("UPPER(TRIM(CONCAT(`mbr_first_name`,' ', `mbr_mid_name` , ' ',`mbr_last_name`))) = ?", strtoupper(vn_to_str($mbr_name)))
                    ->where('dob', $dob)->first();
        if($HbsMember == null){
            return $this->sendError(__('frontend.invalid_effect_member'), 99 );
        }
        $mbr_no = $HbsMember->mbr_no;
        
        $MobileUser = MobileUser::where('mbr_no', $mbr_no )->count();
        if ($MobileUser != 0) {
            return $this->sendError(__('frontend.account_exist'), 101 );
        }

        
        if($HbsMember->age < config('min_age_use_app')){
            return $this->sendError(__('frontend.member_not_exist'), 101 );
        }
        
        try {
            DB::beginTransaction();
            $password = Str::random(8);
            $fullname = $HbsMember->mbr_first_name ? $HbsMember->mbr_first_name ." " : "";
            $fullname = $HbsMember->mbr_mid_name ? $fullname . $HbsMember->mbr_mid_name . " " : $fullname ; 
            $fullname = $HbsMember->mbr_first_name ? $fullname . $HbsMember->mbr_first_name . " " : $fullname ; 
            $MobileUser = MobileUser::create([
                'pocy_no' => $HbsMember->pocy_no,
                'mbr_no' => $HbsMember->mbr_no,
                'password' => hashpass($password),
                'fullname' => trim($fullname)  ,
                'tel' => $HbsMember->tel,
                'email' => $request->email,
                'is_policy_holder' => $HbsMember->is_policy_holder,
                'language' => "_vi",
                'address' => $HbsMember->address,
                'fb_id' => $fb_id,
                'gg_id' => $gg_id
            ]);
            $data['contents'] = sprintf(__('frontend.create_mobile_user_message'),$fullname,config('app.name'),$request->email, $password);
            sendEmail($MobileUser, $data,'templateEmail.noTeamplate', __('frontend.create_mobile_user_subject'));
            DB::commit();
            
            return $this->sendResponse($MobileUser , sprintf( __('frontend.register_success'), $request->email) , 0);
        } catch (Exception $e) {
            Log::error(generateLogMsg($e));
            DB::rollback();
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
    }
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        //valid parameter 
        $validator = Validator::make($request->all(), [
            'email' => 'email|required_without_all:fb_id,gg_id',
            'password' => 'required_with:email',
            'fb_id' => 'required_without_all:email,gg_id',
            'gg_id' => 'required_without_all:email,fb_id'
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 201 );       
        }
        if($request->email != null){
            $user = MobileUser::where('email', $request->email)->where('password', hashpass($request->password))->first();
        }elseif($request->fb_id != null){
            $user = MobileUser::where('fb_id', $request->fb_id)->first();
        }else{
            $user = MobileUser::where('gg_id', $request->gg_id)->first();
        }
        
        if($user != null){
            $success['token'] =  $user->createToken('mobile')->accessToken; 
            $success['user'] =  $user;
            return $this->sendResponse($success, __('frontend.logined') ,200);
        }else{ 
            return $this->sendError(__('auth.failed'), 401);
        }
    }

    /**
     * forget-password
     *
     * @return \Illuminate\Http\Response
     */
    public function forget_password(Request $request){
        //valid parameter 
        $validator = Validator::make($request->all(), [
            'email' => 'email|required'
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 201 );       
        }

        $user = MobileUser::where('email', $request->email)->first();
        if($user == null){
            return $this->sendError(__('frontend.account_not_exist'), 2);
        }
        try {
            DB::beginTransaction();
            $password = Str::random(8);
            $user->password = hashpass($password);
            $user->save;
            $data['contents'] = sprintf(__('frontend.create_reset_password_request_message'),$user->fullname,config('app.name'),$request->email, $password);
            sendEmail($user, $data,'templateEmail.noTeamplate', sprintf(__('frontend.create_reset_password_request_subject'), config('app.name')));
            DB::commit();
            return $this->sendResponse( true, __('frontend.reset_created') , 0);
        } catch (Exception $e) {
            Log::error(generateLogMsg($e));
            DB::rollback();
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
    }

    /**
     * photo api
     *
     * @return \Illuminate\Http\Response
     */
    public function photo(Request $request)
    {
        //dd($request);
        $validator = Validator::make($request->all(), [
            'photo.contents' => 'required|base64',
        ]);
        
        if($validator->fails()){
            return $this->sendError(__('frontend.invalid_photo') , 201 );       
        }
        $user = Auth::user();
        $path = config('constants.photoUpload');
        try {
            DB::beginTransaction();
            $file_name = saveImageBase64 ($request->photo['contents'] , $path , $user->photo);
            $user->photo = $file_name;
            $user->save();
            DB::commit();
            return $this->sendResponse( true, __('frontend.photo_updated') , 0);
        } catch (Exception $e) {
            Log::error(generateLogMsg($e));
            DB::rollback();
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
    }

    /**
     * info api
     *
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        $user = Auth::user();
        $HbsMember = HbsMember::where('mbr_no',$user->mbr_no)->where('company',$user->company)->get()->toArray();
        if(empty($HbsMember)){
            return $this->sendError(__('frontend.internal_server_error'), 500 );
        }
        $pocyYears = [];
        foreach ($HbsMember as $row) {
            $item = $row;
            $key = strtotime($row['memb_eff_date']) + strtotime($row['memb_exp_date']);
            $pocyYears[$key] = isset($pocyYears[$key]) ? $pocyYears[$key] : $row;
            $pocyYears[$key]['plans'] = isset($pocyYears[$key]['plans'])? $pocyYears[$key]['plans'] : explode(';;;', $row['plan_desc']);
            $pocyYears[$key]['events'] = isset($pocyYears[$key]['events']) ? $pocyYears[$key]['events'] : [];
            $item['memb_rstr'] = trim($item['memb_rstr']);
            if (!in_array($item['memb_rstr'], $pocyYears[$key]['events']))
            {
                $pocyYears[$key]['events'][] = $item['memb_rstr'];
            }
            $pocyYears[$key]['events_vi'] = isset($pocyYears[$key]['events_vi']) ? $pocyYears[$key]['events_vi'] : [];
            $item['memb_rstr_vi'] = trim($row['memb_rstr_vi']);
            if (!in_array($item['memb_rstr_vi'], $pocyYears[$key]['events_vi']))
            {
                $pocyYears[$key]['events_vi'][] = $row['memb_rstr_vi'];
            }
            
        }
        krsort($pocyYears);
        $years = [];
        foreach ($pocyYears as $year => $pocyYear)
        {
            if (isset($years['last']))
            {
                $years['previous'] = $pocyYear;
            }
            else
            {
                $years['last'] = $pocyYear;
            }
        }

        foreach ($years as $key => &$pocyYear)
        {
            $pocyYear['insured_periods'] = explode(', ', $pocyYear['insured_periods']);
            foreach ($pocyYear['insured_periods'] as $no => $period)
            {
                if (strlen($period) == 0)
                {
                    unset($pocyYear['insured_periods'][$no]);
                }
            }
            $pocyYear['memb_rstr'] = null;
            $pocyYear['memb_rstr_vi'] = null;

            $date = new \DateTime('now');
            if ($pocyYear['payment_mode'] == 'Semi-Annual')
            {
                // $member_eff = new \DateTime($pocyYear['memb_eff_date']);
                $member_eff = new \DateTime($pocyYear['memb_exp_date']);
                $member_eff->modify('-12 months')->modify('+1 day');
                $member_eff->modify('+6 months')->modify('-1 day');
                $pocyYear['payment_exp'] = $member_eff->format('Y-m-d');

                if ($pocyYear['payment_exp'] < $date->format('Y-m-d'))
                {
                    if ($pocyYear['policy_status'] == "Second payment Policy & Health Card Released")
                    {
                        $pocyYear['policy_status'] = "First payment Policy was expired";
                        $pocyYear['request_next_payment'] = true;
                    }
                    elseif ($pocyYear['policy_status'] == "Approved")
                    {
                        $pocyYear['payment_exp'] = $pocyYear["memb_exp_date"];
                        $pocyYear['request_next_payment'] = false;
                    }
                }
                elseif ($pocyYear['policy_status'] == "Second payment Policy & Health Card Released")
                {
                    $pocyYear['policy_status'] == "First payment Policy & Health Card Released"; // 1 month previous before first payment expired
                }
            }
            else
            {
                
                $member_eff = new \DateTime($pocyYear['memb_exp_date']);
                $member_eff->modify('-12 months')->modify('+1 day'); // pocy_eff_date
                $member_eff->modify('+12 months')->modify('-1 day');
                $pocyYear['payment_exp'] = $member_eff->format('Y-m-d');
                $pocyYear['request_next_payment'] = false;
            }
            $exp_date = new \DateTime($pocyYear['payment_exp']);
            $exp_date->modify('+13 months')->format('Y-m-d');
            if ($exp_date > $date->format('Y-m-d'))
            {
                $pocyYear['can_claim'] = true;
            }
            else
            {
                $pocyYear['can_claim'] = false;
            }
        }
        return $this->sendResponse( $years, "OK" , 0);
    }
    
     /**
     * insurance-card api
     *
     * @return \Illuminate\Http\Response
     */
    public function insurance_card(Request $request){
        $user = Auth::user();
        $lang = $this->lang;
        $HbsMember = HbsMember::where('mbr_no',$user->mbr_no)->where('company',$user->company)->whereNotNull('ben_schedule')->orderBy('memb_eff_date', 'DESC')->first()->toArray();
        $PcvInsuredCardBuilder = new PcvInsuredCardBuilder($HbsMember,$lang);
        return $this->sendResponse( $PcvInsuredCardBuilder->get(), "OK" , 0);
    }


    public function test1(Request $request){
        
        $url_file = resource_path('sql/import_pcv_member.sql');
        $sql =  file_get_contents($url_file);
        $url_file2 = resource_path('sql/pcv_benefit_detail.sql');
        $sql_detail =  file_get_contents($url_file2);
        $benefits = [];
        $HbsMember = DB::connection('hbs_pcv')->select($sql);
        foreach ($HbsMember as $key => $item) {
            
            $item = json_decode(json_encode($item), true);
            
            $benSchedule = [];
            $item = $this->getExtra($item, $benefits, $sql_detail);
            dd($item);
            
        }
    }


    private function getExtra($item, &$benefits , $sql_detail){
        $sql2 = "BEGIN PKG_RP.sp_benefit_schedule(:mplOid, :cur); END;";
        $conn = oci_connect(
            config('oracle.hbs_pcv.username'),
            config('oracle.hbs_pcv.password'),
            config('oracle.hbs_pcv.host') . '/' . config('oracle.hbs_pcv.database')
        );
        $cursor = oci_new_cursor($conn);

        $item['benefit_en'] = '';
        $item['benefit_vi'] = '';


        if (!isset($benefits[$item['mbr_no']]))
        {
            $benefits[$item['mbr_no']] = [];
        }
        $hasBenefit = false;
        if (!isset($benefits[$item['mbr_no']][$item['memb_eff_date']]))
        {
            $benefits[$item['mbr_no']][$item['memb_eff_date']] = [
                'benefit_en' => '',
                'benefit_vi' => ''
            ];
        }
        elseif ($benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_en'] !== '')
        {
            $item['benefit_en'] = $benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_en'];
            $item['benefit_vi'] = $benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_vi'];
            $hasBenefit = true;
        }

        if ($hasBenefit)
        {
            return $item;
        }

        try
        {
            $mplid = $item['mepl_oid'];
            $stid = oci_parse($conn, $sql2);
            oci_bind_by_name($stid, ":mplOid", $mplid);
            oci_bind_by_name($stid, ":cur", $cursor, -1, OCI_B_CURSOR);
            oci_execute($stid);
            oci_execute($cursor);
            $benSchedule = [];
            while (($row = oci_fetch_array($cursor, OCI_ASSOC + OCI_RETURN_NULLS)) != false)
            {
                $benSchedule["tmp"] = $row;
            }
        }
        catch (Exception $e)
        {
            return $item;
        }
        $item['ben_schedule'] = null;
        if (!empty($benSchedule))
        {
            
            
            $benDetails = DB::connection('hbs_pcv')->select($sql_detail, [$benSchedule['tmp']['PLAN_OID']]);
            $benDetails = json_decode(json_encode($benDetails), true);
            if (!empty($benDetails))
            {
                $benSchedule['detail'] = $benDetails[0];
            }
            else
            {
                $benSchedule['detail']['copay'] = null;
                $benSchedule['detail']['amtperday'] = null;
                $benSchedule['detail']['amtpervis'] = null;
            }

            $item['ben_schedule'] = json_encode($benSchedule);
        }
        // $langs = ['en', 'vi'];
        // if (!$hasBenefit && $item['ben_schedule'] !== null)
        // {
        //     try
        //     {
        //         foreach ($langs as $lang)
        //         {
        //             // $builder = DIContainer::resolve(PcvBenefitBuilder::class, $item, $lang);
                    
        //             $builder = new PcvBenefitBuilder($item, $lang);
        //             $benefit = $builder->get();
        //             $item['benefit_' . $lang] = $benefit === null ? null : json_encode($benefit);
        //             $benefits[$item['mbr_no']][$item['memb_eff_date']]['benefit_' . $lang] = $item['benefit_' . $lang];
        //         }
        //     }
        //     catch (Exception $e)
        //     {
        //         dd('lỗi');
        //     }
        // }
        return $item;
    }
}