<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HbsMember extends Model
{
    use HasFactory;
    public $table = 'hbs_member';
    protected $guarded = ['id'];
    
    public function getAgeAttribute(){
        $dbDate = Carbon::parse($this->dob);
        $diffYears = Carbon::now()->diffInYears($dbDate);
        return $diffYears;
    }
}
