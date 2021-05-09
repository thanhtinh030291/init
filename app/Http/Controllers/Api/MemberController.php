<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
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

class MemberController extends BaseController
{
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
        $data['contents'] = "sdsdsds";
        sendEmail('tinh', $data,'templateEmail.noTeamplate', __('frontend.create_mobile_user_subject'));dd(1);
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

        $is_member = HbsMember::where('pocy_no', $request->pocy_no)
                    ->whereRaw("UPPER(TRIM(CONCAT(`mbr_first_name`,' ', `mbr_mid_name` , ' ',`mbr_last_name`))) = ?", strtoupper(vn_to_str($mbr_name)))
                    ->where('dob', $dob)->get();
        if($is_member == null){
            return $this->sendError(__('frontend.invalid_effect_member'), 99 );
        }
        $mbr_no = $is_member->mbr_no;
        
        $MobileUser = MobileUser::where('mbr_no', $mbr_no )->orWhere('email', $request->email)->count();
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
            return $this->sendResponse($success, 'User login successfully.',200);
        }else{ 
            return $this->sendError('Unauthorised.', 201);
        }
    }

    /**
     * photo api
     *
     * @return \Illuminate\Http\Response
     */
    public function photo(Request $request,$mbr_no)
    {
        //$photo['contents']
        //valid parameter 
        $validator = Validator::make($request->all(), [
            'photo.contents' => 'required|base64',
        ]);
        
        if($validator->fails()){
            return $this->sendError(__('frontend.invalid_photo') , 201 );       
        }
        dd(1);
        $file_name = saveImageBase64 ($request->photo['contents'] , $path );
    }
}