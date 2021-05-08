<?php


namespace App\Http\Controllers\Api;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;




class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message , $code)
    {
        $response = [
            'success' => true,
            'code' => $code,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }


    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($message = [], $code = 404)
    {
        
        if(!is_array($message)){
            $errorMessages = $message;
        }else{
            $errorMessages = implode('<br />', $message);
        }
        $response = [
            'success' => false,
            'code' => $code,
            'message' => $errorMessages,
        ];
        return response()->json($response, 200);
    }
}