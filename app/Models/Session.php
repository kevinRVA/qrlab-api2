<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    // LA NUEVA MAGIA: Ahora solo guardamos el ID de la sección y el laboratorio
    protected $fillable = [
        'section_id',
        'laboratory_name',
        'qr_token',
        'is_active',
    ];

    // Relación: Esta clase (sesión) pertenece a una Sección específica
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    // Relación: Esta clase tiene muchas asistencias de alumnos
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}