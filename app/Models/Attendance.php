<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    // AQUÍ ESTÁ LA SOLUCIÓN: Le damos permiso a Laravel de guardar estos dos IDs
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