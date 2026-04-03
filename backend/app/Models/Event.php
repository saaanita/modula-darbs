<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'day',
        'month',
        'year',
        'time',
        'is_all_day',
        'user_id',
        'group_id',
        'category_id'
    ];
}