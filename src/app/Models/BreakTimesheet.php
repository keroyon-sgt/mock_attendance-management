<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTimesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'timesheet_id',
        'date',
        'break_in',
        'break_out',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function timesheets()
    {
        return $this->hasMany('App\Models\Timesheet');
    }
}
