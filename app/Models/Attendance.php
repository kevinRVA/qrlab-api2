<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['session_id', 'student_name', 'student_code', 'career'];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }
}
