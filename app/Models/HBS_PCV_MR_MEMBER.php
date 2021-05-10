<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HBS_PCV_MR_MEMBER extends  BaseModelHbsPcv
{
    protected $table = 'MR_MEMBER';
    protected $guarded = ['id'];
    protected $primaryKey = 'memb_oid';
    
}
