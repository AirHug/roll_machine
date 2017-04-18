<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['userNum', 'date', 'lateMinute', 'lateTime', 'earlyMinute', 'earlyTime', 'availableDay'];
}
