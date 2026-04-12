<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = ['section_id', 'student_id'];

    // Una inscripción pertenece a una sección
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // Una inscripción pertenece a un estudiante
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}