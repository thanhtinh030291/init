<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public static function getListIncharge()
    {
        return self::orderBy('id', 'desc')->pluck('name', 'id');
    }
    public function messagesSent()
    {
    return $this->hasMany('App\Message', 'user_from', 'id');
    }

    public function messagesReceiver()
    {
    return $this->hasMany('App\Message', 'user_to', 'id');
    }

    public function getMessagesReceiverAttribute()
    {
        $messages = $this->messagesReceiver()->with("userFrom")->where('is_read', 0)->latest()->get();
        return $messages;
    }
    
    public function getMessagesReceiver10Attribute()
    {
        $messages = $this->messagesReceiver()->with("userFrom")->latest()->limit(10)->get();
        return $messages;
    }

    public function getMessagesSent10Attribute()
    {
        $messages = $this->messagesSent()->latest()->limit(10)->get();
        return $messages;
    }
}
