<?php

namespace App\Models;

use Yajra\Oci8\Eloquent\OracleEloquent as Eloquent;

class BaseModelHbsBsh extends Eloquent
{
    protected $connection = 'hbs_bsh';
    public $timestamps = false;
}
