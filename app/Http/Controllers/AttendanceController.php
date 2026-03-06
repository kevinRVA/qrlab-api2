<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // Ahora guardamos el ID de la sesión y el ID del estudiante
    protected $fillable = ['session_id', 'student_id'];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}