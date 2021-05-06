<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanHbsConfig extends BaseModel
{
    use HasFactory;
    protected $guarded = ['id'];
    public $table = 'plan_hbs_config';
    protected static $table_static = 'plan_hbs_config';
    protected $dates = ['deleted_at'];
    
}
