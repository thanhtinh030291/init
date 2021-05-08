<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController;
use App\Models\MobileUser;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Validation\Rule;

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
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User register successfully.');
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

        $user = MobileUser::first();
        if($user){
            $success['token'] =  $user->createToken('MyApp')->accessToken; 
            $success['user'] =  $user;
            return $this->sendResponse($success, 'User login successfully.',200);
        }else{ 
            return $this->sendError('Unauthorised.', 201);
        }
    }
}