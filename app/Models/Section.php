<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['subject_id', 'teacher_id', 'section_code', 'schedule'];

    // Relación: Una sección pertenece a una materia
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Relación: Una sección es impartida por un docente
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    // Relación: Estudiantes instructores (tutores) asignados a esta sección
    public function instructors()
    {
        return $this->belongsToMany(User::class, 'section_instructors');
    }
}