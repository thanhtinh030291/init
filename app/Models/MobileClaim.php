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

    public $table = 'mobile_user';
    protected $guarded = ['id'];
    public $timestamps = false;
    

    
    protected $casts = [
        'id' => 'string'
    ];


}
