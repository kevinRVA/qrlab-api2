<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    // AQUÍ ESTÁ LA MAGIA: Si no pones laboratory_name aquí, Laravel lo ignora al guardar
    protected $fillable = [
        'teacher_name',
        'teacher_code',
        'subject',
        'section',
        'laboratory_name', // <-- ¡Asegúrate de tener esta línea!
        'qr_token',
        'is_active',
    ];

    // ... aquí abajo sigue tu relación attendances()
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}