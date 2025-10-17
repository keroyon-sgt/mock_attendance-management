<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'punch_in',
        'punch_out',
        'break1_in',
        'break1_out',
        'break2_in',
        'break2_out',
        'remark',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
