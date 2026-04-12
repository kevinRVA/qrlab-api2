<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabVisit extends Model
{
    protected $fillable = [
        'laboratory_id',
        'student_id',
        'entry_time',
        'exit_time',
        'auto_closed',
        'no_exit_warning',
    ];

    protected $casts = [
        'entry_time'       => 'datetime',
        'exit_time'        => 'datetime',
        'auto_closed'      => 'boolean',
        'no_exit_warning'  => 'boolean',
    ];

    // La visita pertenece a un laboratorio
    public function laboratory()
    {
        return $this->belongsTo(Laboratory::class);
    }

    // La visita pertenece a un estudiante
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
