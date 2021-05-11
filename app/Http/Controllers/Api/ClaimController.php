<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\MobileClaim;
use Illuminate\Support\Facades\Auth;

class ClaimController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        
    }
    /**
     * Get a list of Claims of a mobile user
     *
     * @return \Illuminate\Http\Response
     */
    public function issues()
    {
        $user = Auth::user();
        $issues = MobileClaim::join('mobile_claim_status', 'mobile_claim_status.id', '=', 'mobile_claim_status_id')
            ->where('mobile_user_id', $user->id)
            ->get()->toArray();
        return $this->sendResponse($issues, 'OK', 0); 
    }
}
