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

        $MobileUser = MobileUser::where('mbr_no', $HbsMember[0]->mbr_no )->where('email', $request->email)->count();
        // if ($MobileUser != 0) {
        //     return $this->sendError(__('frontend.account_exist'), 101 );
        // }

        $HbsMember = HbsMember::where('pocy_no', $request->pocy_no)->where('email', $request->email)->first();
        if($HbsMember->age < config('min_age_use_app')){
            return $this->sendError(__('frontend.member_not_exist'), 101 );
        }
        
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
            $data['contents'] = 'tinh test';
            sendEmail($MobileUser,$data ,'templateEmail.noTeamplate', 'Dang ky');
        dd(1);
        try {
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
            sendEmail($MobileUser,'tinh','templateEmail.noTeamplate', 'Dang ky');
            return $this->sendResponse($success, 'User register successfully.');
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
        
        if($user){
            $success['token'] =  $user->createToken('MyApp')->accessToken; 
            $success['user'] =  $user;
            return $this->sendResponse($success, 'User login successfully.',200);
        }else{ 
            return $this->sendError('Unauthorised.', 201);
        }
    }
}