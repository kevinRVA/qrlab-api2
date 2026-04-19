<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $fillable = ['name', 'qr_token'];

    // Un laboratorio tiene muchas visitas voluntarias
    public function visits()
    {
        return $this->hasMany(LabVisit::class);
    }

    // Los coordinadores asignados a este laboratorio
    public function coordinators()
    {
        return $this->belongsToMany(User::class, 'coordinator_labs');
    }
}
