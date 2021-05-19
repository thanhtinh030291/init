<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends BaseModel
{
    
    use HasFactory;
    //use HasApiTokens2;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $table = 'provider';
    protected $guarded = ['id'];
    

    
    protected $casts = [
        'id' => 'string'
    ];

}
