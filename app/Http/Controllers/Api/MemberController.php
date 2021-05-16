<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MobileUser;
use App\Models\HbsMember;
use App\Models\MobileUserBankAccount;
use App\Models\MobileDevice;
use App\Models\PlanHbsConfig;
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
     * post
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'pocy_no' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400);       
        }
        
        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->count();
        
        if($HbsMember == 0){
            return $this->sendError(__('frontend.pocy_not_exist') , 400, 400 );       
        }

        $HbsMember = HbsMember::select('mbr_no','company')->where('pocy_no', $request->pocy_no)->where('email', $request->email)->distinct()->get();
        if($HbsMember->count() != 1){
            return $this->sendError('Please use eKYC', 10 , 200 );       
        }

        $MobileUser = MobileUser::where('mbr_no', $HbsMember[0]->mbr_no )->orWhere('email', $request->email)->count();
        if ($MobileUser != 0) {
            return $this->sendError(__('frontend.account_exist'), 405 , 400);
        }

        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->where('email', $request->email)->first();
        if($HbsMember->age < config('min_age_use_app')){
            return $this->sendError(__('frontend.member_not_exist'), 406 , 400);
        }
        
        try {
            DB::beginTransaction();
            $password = $env = config('app.debug') == false ? Str::random(8) : "123456xx";
            $fullname = $HbsMember->mbr_first_name ? $HbsMember->mbr_first_name ." " : "";
            $fullname = $HbsMember->mbr_mid_name ? $fullname . $HbsMember->mbr_mid_name . " " : $fullname ; 
            $fullname = $HbsMember->mbr_last_name ? $fullname . $HbsMember->mbr_first_name . " " : $fullname ; 
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
            //sendEmail($MobileUser, $data,'templateEmail.noTeamplate', __('frontend.create_mobile_user_subject'));
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
     * post
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
            return $this->sendError($validator->errors()->all() , 400 ,400 );       
        }
        $lang = App::isLocale('en') ? 'en' : 'vi';
        
        $fb_id = $request->fb_id;
        $gg_id = $request->gg_id;
        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->count();
        
        if($HbsMember == 0){
            return $this->sendError(__('frontend.pocy_not_exist') , 400 ,400);       
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
                return $this->sendError($response_ocr->errorMessage , 400 ,400 );
            }else{
                if(isset($response_ocr->data[1])){
                    $mbr_name = strtoupper(vn_to_str($response_ocr->data[1]->info->name));
                    $dob = $response_ocr->data[1]->info->dob;
                }else{
                    return $this->sendError(__("frontend.id_font_requid") , 400 , 400 );
                }
            }
            //match_face
            $response_match = $client->request("POST", config("constants.URL_API_EKYC")."/api/v2/ekyc/face_matching?type1=card&format_type=base64&is_thumb=false" , ['json'=>$body_face_matching]);
            $response_match =  json_decode($response_match->getBody()->getContents());
            
            if($response_match->data->invalidCode != 0 && $response_match->data->matching <= 75){
                return $this->sendError(config("mess_match_".$lang.".".$response_match->data->invalidCode) , $response_match->data->invalidCode );
            }elseif($response_match->data->matching <= 75){
                return $this->sendError(__('frontend.ekyc_not_match') , 400 , 400);
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
            return $this->sendError(__('frontend.id_font_requid') , 400 , 400 );
        }
        $array_name = explode(" ", strtoupper($mbr_name));

        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->where('dob', $dob);

        foreach ($array_name as $key => $value) {
            $HbsMember = $HbsMember->where(function($q) use($value){
                $q->where('mbr_last_name','like', '%' . $value . '%')
                  ->orWhere('mbr_mid_name','like', '%' . $value . '%')
                  ->orWhere('mbr_first_name','like', '%' . $value . '%');
            });
        }
        $HbsMember = $HbsMember->first();
        if($HbsMember == null){
            return $this->sendError(__('frontend.invalid_effect_member'), 400 , 400 );
        }

        $mbr_no = $HbsMember->mbr_no;
        $MobileUser = MobileUser::where('mbr_no', $mbr_no )->count();
        if ($MobileUser != 0) {
            return $this->sendError(__('frontend.account_exist'), 400 , 400 );
        }

        
        if($HbsMember->age < config('min_age_use_app')){
            return $this->sendError(__('frontend.member_not_exist'), 400 , 400 );
        }
        
        try {
            DB::beginTransaction();
            $password = $env = config('app.debug') == false ? Str::random(8) : "123456xx";
            $fullname = $HbsMember->mbr_first_name ? $HbsMember->mbr_first_name ." " : "";
            $fullname = $HbsMember->mbr_mid_name ? $fullname . $HbsMember->mbr_mid_name . " " : $fullname ; 
            $fullname = $HbsMember->mbr_last_name ? $fullname . $HbsMember->mbr_first_name . " " : $fullname ; 
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
     * post
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
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
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
            return $this->sendResponse($success, __('frontend.logined') , 0 );
        }else{ 
            return $this->sendError(__('auth.failed'), 401);
        }
    }

    /**
     * forget-password
     * post
     * @return \Illuminate\Http\Response
     */
    public function forget_password(Request $request){
        //valid parameter 
        $validator = Validator::make($request->all(), [
            'email' => 'email|required'
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }

        $user = MobileUser::where('email', $request->email)->first();
        if($user == null){
            return $this->sendError(__('frontend.account_not_exist'), 400 , 400);
        }
        try {
            DB::beginTransaction();
            $password = $env = config('app.debug') == false ? Str::random(8) : "123456xx";
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
     * forget-password
     * post
     * @return \Illuminate\Http\Response
     */
    public function password(Request $request){
        //valid parameter 
        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }
        $user = Auth::user();
        if($user->email == null){
            return $this->sendError(__('frontend.not_register_by_email') , 400 , 400 );
        }

        if(hashpass($request->old_password) !=  $user->password){
            return $this->sendError(__('frontend.invalid_old_pass') , 400 , 400 );
        }
        
        $MobileUser = MobileUser::where('id' , $user->id)->update([
            'password' => hashpass($request->new_password),
        ]);
        return $this->sendResponse( $MobileUser, __('frontend.password_updated') , 0);
        
    }


    /**
     * photo api
     * patch
     * @return \Illuminate\Http\Response
     */
    public function photo(Request $request)
    {
        //dd($request);
        $validator = Validator::make($request->all(), [
            'photo.contents' => 'required|base64',
        ]);
        
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );     
            //return $this->sendError(__('frontend.invalid_photo') , 400 , 400 );       
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
     * get
     * @return \Illuminate\Http\Response
     */
    public function info(Request $request)
    {
        $user = Auth::user();
        $HbsMember = HbsMember::where('mbr_no',$user->mbr_no)->where('company',$user->company)->get()->toArray();
        $years = [];
        $years = $this->getInfo($user->mbr_no, $user->company);

        $file_base64 = $user->photo ? getImageBase64(config('constants.photoUpload').$user->photo) : null;
        $years['photo'] = $file_base64;
        return $this->sendResponse( $years, "OK" , 0);
    }

    /**
     * info api
     * get
     * @return \Illuminate\Http\Response
     */
    public function full_info(Request $request)
    {
        $user = Auth::user();
        $HbsMember = HbsMember::where('mbr_no', '022504400')->where('company',$user->company)->get()->toArray();
        $years = [];
        $years = $this->getInfo($user->mbr_no, $user->company);
        if($HbsMember[0]['children'] == null){
            $years['children'] = null;
        }else{
            $children = explode(';', $HbsMember[0]['children']);

            foreach ($children as $child)
            {
                list($mbrNo, $mbrName, $mbrAge) = explode(' - ', $child);
                if (intval($mbrAge) < config('constants.majorityAge') && intval($mbrNo) != intval($user->mbr_no))
                {
                    $years['children'][] = [
                        'mbr_no' => $mbrNo,
                        'mbr_name' => ucwords($mbrName),
                        'info' => $this->getInfo($mbrNo , $user->company)
                    ];
                }
            }
        };
        return $this->sendResponse( $years, "OK" , 0);
    }
    
     /**
     * insurance-card api
     * get
     * @return \Illuminate\Http\Response
     */
    public function insurance_card(Request $request){
        $user = Auth::user();
        $lang = App::currentLocale();
        $HbsMember = HbsMember::where('mbr_no',$user->mbr_no)->where('company',$user->company)->whereNotNull('ben_schedule')->orderBy('memb_eff_date', 'DESC')->first()->toArray();
        $PlanHbsConfig = PlanHbsConfig::where('plan_id', data_get($HbsMember,'plan_id'))->where('rev_no', data_get($HbsMember,'rev_no'))
        ->where('company', $user->company)->first();
        $info = $this->getInfo($user->mbr_no, $user->company);
        $PcvInsuredCardBuilder = new PcvInsuredCardBuilder($HbsMember,$lang);
        $data['card'] = $PcvInsuredCardBuilder->get(); 
        $data['fullname'] = data_get($info,'last.fullname');
        $data['can_claim'] = data_get($info,'last.can_claim');
        $data['payment_exp'] = data_get($info,'last.payment_exp');
        $date = Carbon::createFromFormat("Y-m-d", $data['payment_exp']);
        $data['mss']  = $date->isPast() ? __('frontend.pocy_expired', ['date' => $data['payment_exp']]): null;
        if($HbsMember['children'] == null){
            $data['children'] = null;
        }else{
            $children = explode(';', $HbsMember['children']);
            foreach ($children as $child)
            {
                list($mbrNo, $mbrName, $mbrAge) = explode(' - ', $child);
                if (intval($mbrAge) < config('constants.majorityAge') && intval($mbrNo) != intval($user->mbr_no))
                {
                    $HbsMember = HbsMember::where('mbr_no',$mbrNo)->where('company',$user->company)->first()->toArray();
                    $info = $this->getInfo($user->mbr_no, $user->company);
                    $date = Carbon::createFromFormat("Y-m-d", data_get($info,'last.payment_exp'));
                    $PlanHbsConfig = PlanHbsConfig::where('plan_id', data_get($HbsMember,'plan_id'))->where('rev_no', data_get($HbsMember,'rev_no'))
                    ->where('company', $user->company)->first();
                    $PcvInsuredCardBuilder = new PcvInsuredCardBuilder($HbsMember,$lang);
                    $data['children'][] = [
                        'mbr_no' => $mbrNo,
                        'mbr_name' => ucwords($mbrName),
                        'fullname' => data_get($info,'last.fullname'),
                        'file_id'  => $PlanHbsConfig ? $PlanHbsConfig->id : null,
                        'mss' => $date->isPast() ? __('frontend.pocy_expired', ['date' => $data['payment_exp']]): null,
                        'can_claim' => data_get($info,'last.can_claim'),
                        'payment_exp' => data_get($info,'last.payment_exp'),
                        'card' => $PcvInsuredCardBuilder->get(),
                    ];
                }
            }
        }
        
        return $this->sendResponse( $data, "OK" , 0);
    }

    /**
     * benefit api
     * get
     * @return \Illuminate\Http\Response
     */
    public function benefit(Request $request){
        $user = Auth::user();
        $lang = App::currentLocale();
        $HbsMember = HbsMember::where('mbr_no',$user->mbr_no)->where('company',$user->company)->first()->toArray();
        $PlanHbsConfig = PlanHbsConfig::where('plan_id', data_get($HbsMember,'plan_id'))->where('rev_no', data_get($HbsMember,'rev_no'))
        ->where('company', $user->company)->first();
        $info = $this->getInfo($user->mbr_no, $user->company);

        $data['fullname'] = data_get($info,'last.fullname');
        $data['can_claim'] = data_get($info,'last.can_claim');
        $data['payment_exp'] = data_get($info,'last.payment_exp');
        $data['benefit'] = json_decode($HbsMember['benefit_' . $lang],true);
        $date = Carbon::createFromFormat("Y-m-d", $data['payment_exp']);
        $data['mss']  = $date->isPast() ? __('frontend.pocy_expired', ['date' => $data['payment_exp']]): null;
        $data['file_id'] = $PlanHbsConfig ? $PlanHbsConfig->id : null;
        $data['ready'] = $PlanHbsConfig ? $PlanHbsConfig->is_benefit_ready : 0;
        if($HbsMember['children'] == null){
            $data['children'] = null;
        }else{
            $children = explode(';', $HbsMember['children']);
            foreach ($children as $child)
            {
                list($mbrNo, $mbrName, $mbrAge) = explode(' - ', $child);
                if (intval($mbrAge) < config('constants.majorityAge') && intval($mbrNo) != intval($user->mbr_no))
                {
                    $HbsMember = HbsMember::where('mbr_no',$mbrNo)->where('company',$user->company)->first()->toArray();
                    $info = $this->getInfo($user->mbr_no, $user->company);
                    $date = Carbon::createFromFormat("Y-m-d", data_get($info,'last.payment_exp'));
                    $PlanHbsConfig = PlanHbsConfig::where('plan_id', data_get($HbsMember,'plan_id'))->where('rev_no', data_get($HbsMember,'rev_no'))
                    ->where('company', $user->company)->first();
                    $data['children'][] = [
                        'mbr_no' => $mbrNo,
                        'mbr_name' => ucwords($mbrName),
                        'fullname' => data_get($info,'last.fullname'),
                        'file_id'  => $PlanHbsConfig ? $PlanHbsConfig->id : null,
                        'ready' => $PlanHbsConfig ? $PlanHbsConfig->is_benefit_ready : 0,
                        'mss' => $date->isPast() ? __('frontend.pocy_expired', ['date' => $data['payment_exp']]): null,
                        'can_claim' => data_get($info,'last.can_claim'),
                        'payment_exp' => data_get($info,'last.payment_exp'),
                        'benefit' => json_decode($HbsMember['benefit_' . $lang],true)
                    ];
                }
            }
        };    
        return $this->sendResponse( $data, "ok", 0);
    }
    
    /**
     *  bank-accounts
     *  get
     * @return \Illuminate\Http\Response
     */
    public function bank_accounts(Request $request){
        $user = Auth::user();
        $lang = App::currentLocale();
        $MobileUserBankAccount = MobileUserBankAccount::where('mobile_user_id',$user->id)->get();
        return $this->sendResponse( $MobileUserBankAccount, "OK" , 0);
    }

    /**
     *  bank-account
     *  Post
     * @return \Illuminate\Http\Response
     */
    public function bank_account_create(Request $request){
        $validator = Validator::make($request->all(), [
            'bank_acc_name' => 'required',
            'bank_acc_no' => 'required',
            'bank_name' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }

        $user = Auth::user();
        $lang = App::currentLocale();
        $MobileUserBankAccount = MobileUserBankAccount::where('mobile_user_id',$user->id)->count();
        if($MobileUserBankAccount >= 1){
            return $this->sendError(__("frontend.bank_account_exist") , 400 , 400 );    
        }
        $data = [
            'bank_name' => $request->bank_name,
            'bank_address' => $request->bank_address,
            'bank_acc_no' => $request->	bank_acc_no,
            'bank_acc_name' => $request->bank_acc_name,
            'mobile_user_id' => $user->id,
            'crt_by' => $user->fullname,
            'upd_by' => $user->fullname,
        ];
        $data = MobileUserBankAccount::create($data);
        return $this->sendResponse( $data, __("frontend.bank_account_added") , 0);
    }

    /**
     *  bank-account
     *  Put
     * @return \Illuminate\Http\Response
     */
    public function bank_account_update(Request $request){
        $validator = Validator::make($request->all(), [
            'bank_acc_name' => 'required',
            'bank_acc_no' => 'required',
            'bank_name' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }

        $user = Auth::user();
        $lang = App::currentLocale();
        $MobileUserBankAccount = MobileUserBankAccount::where('mobile_user_id',$user->id)->first();
        if($MobileUserBankAccount == null){
            return $this->sendError(__("frontend.bank_account_not_exist") , 400 , 400 );
        }
        
        $data = [
            'bank_name' => $request->bank_name,
            'bank_address' => $request->bank_address,
            'bank_acc_no' => $request->	bank_acc_no,
            'bank_acc_name' => $request->bank_acc_name,
            'mobile_user_id' => $user->id,
            'upd_by' => $user->fullname
        ];
        $MobileUserBankAccount = MobileUserBankAccount::updateOrCreate([
            'id' => $MobileUserBankAccount->id
        ],$data);
        return $this->sendResponse( $MobileUserBankAccount, __("frontend.bank_account_added") , 0);
    }

    /**
     *  device
     *  Put
     * @return \Illuminate\Http\Response
     */
    public function device(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'type' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError($validator->errors()->all() , 400 , 400 );       
        }

        $user = Auth::user();
        $lang = App::currentLocale();
        $data = [
            'device_token' => $request->token,
            'device_type' => $request->type,
            'mobile_user_id' => $user->id,
            'upd_by' => $user->fullname,
            'crt_by' => $user->fullname
        ];
        $MobileDevice = MobileDevice::where('mobile_user_id',$user->id)
        ->where('device_type',$request->type)
        ->where('device_token',$request->token)
        ->orderBy('updated_at','DESC')->get();
        
        if($MobileDevice->count() == 0){
            MobileDevice::create($data);
        }

        if($MobileDevice->count() > 0){
            MobileDevice::where('mobile_user_id',$user->id)
            ->where('device_type',$request->type)
            ->where('device_token',$request->token)->update($data);
        }
        
        return $this->sendResponse( [], 'ok' , 0);
    }

    private function getInfo($mbr_no, $company){
        $HbsMember = HbsMember::where('mbr_no',$mbr_no)->where('company',$company)->get()->toArray();
        if(empty($HbsMember)){
            return $this->sendError(__('frontend.internal_server_error'), 400,400);
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
            $pocyYears[$key]['fullname'] = $row['mbr_first_name'] .' '. $row['mbr_mid_name'] .' '. $row['mbr_last_name'];
            
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
        return $years;
    }

}