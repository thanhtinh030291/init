<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class BaseModelHbsPcv extends Eloquent
{
    protected $connection = 'hbs_pcv';
    public $timestamps = false;
}
