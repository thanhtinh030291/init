<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HBS_PCV_MR_MEMBER_PLAN extends  BaseModelHbsPcv
{
    protected $table = 'MR_MEMBER_PLAN';
    protected $guarded = ['id'];
    protected $primaryKey = 'mepl_oid';
    
}
