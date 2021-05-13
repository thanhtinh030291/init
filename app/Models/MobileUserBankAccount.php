<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileUserBankAccount extends BaseModel
{
    
    use HasFactory;

    use \App\Http\Traits\UsesUuid;
    //use HasApiTokens2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = 'mobile_user_bank_account';
    protected $guarded = ['id'];
    

    
    protected $casts = [
        'id' => 'string'
    ];

    /**
     * Get the phone associated with the user.
     */
    // public function mobile_status()
    // {
        // return $this->hasOne(MobileClaimStatus::class, 'id' ,'mobile_claim_status_id');
    // }
}
