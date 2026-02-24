<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $fillable = ['teacher_name', 'teacher_code', 'subject', 'section', 'qr_token', 'is_active'];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
