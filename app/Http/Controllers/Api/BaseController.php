<?php


namespace App\Http\Controllers\Api;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;



class BaseController extends Controller
{
    public $lang = null;

    public function __construct()
    {
        $this->lang = App::currentLocale();
    }
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
    public function sendError($message = [], $code = 400 , $status = 401)
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
        return response()->json($response, $status);
    }
}