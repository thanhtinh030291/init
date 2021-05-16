<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MobileClaim extends BaseModel
{
    
    use HasFactory;

    use \App\Http\Traits\UsesUuid;
    //use HasApiTokens2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = 'mobile_claim';
    protected $guarded = ['id'];
    

    
    protected $casts = [
        'id' => 'string'
    ];

    /**
     * Get the phone associated with the user.
     */
    public function mobile_claim_status()
    {
        return $this->hasOne(MobileClaimStatus::class, 'id' ,'mobile_claim_status_id');
    }
    public function mobile_claim_file(){
        return $this->hasMany(MobileClaimFile::class,'mobile_claim_id');
    }
    public function mobile_user()
    {
        return $this->belongsTo(MobileUser::class, 'mobile_user_id', 'id');
    }
}
