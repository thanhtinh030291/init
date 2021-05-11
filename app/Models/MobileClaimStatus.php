<?php

namespace App\Models;

class MobileClaimStatus extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = 'mobile_claim_status';
    protected $guarded = ['id'];
    public $timestamps = false;
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string'
    ];
}
