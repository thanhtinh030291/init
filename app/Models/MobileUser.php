<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens ;


class MobileUser extends Authenticatable
{
    
    use HasFactory;
    use Notifiable;
    use \App\Http\Traits\UsesUuid;
    use HasApiTokens;
    //use HasApiTokens2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = 'mobile_user';
    protected $guarded = ['id'];
    public $timestamps = false;
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public function validateForPassportPasswordGrant($password)
    {

        return true;
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'id' => 'string'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    // protected $appends = [
    //     'profile_photo_url',
    // ];
    public function  mobile_device(){
        return $this->hasMany(MobileDevice::class, 'mobile_user_id', 'id');
    }
    

}
