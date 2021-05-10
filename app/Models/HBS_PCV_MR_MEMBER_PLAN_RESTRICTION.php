<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HBS_PCV_MR_MEMBER_PLAN_RESTRICTION extends  BaseModelHbsPcv
{
    protected $table = 'mr_member_plan_restriction';
    protected $guarded = ['id'];
    protected $primaryKey = 'mers_oid';
    
}
