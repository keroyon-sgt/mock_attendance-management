<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'timesheet_id',
        'break_timesheet_id',
        // 'date',
        'in',
        'out',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function timesheets()
    {
        return $this->belongsTo('App\Models\Timesheet');
    }

    public function breaktimesheets()
    {
        return $this->belongsTo('App\Models\breakTimesheet');
    }
}
