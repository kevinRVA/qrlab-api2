<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laboratory extends Model
{
    protected $fillable = ['name', 'qr_token'];

    protected static function booted()
    {
        static::creating(function ($laboratory) {
            if (empty($laboratory->qr_token)) {
                $laboratory->qr_token = 'LAB-' . strtoupper(\Illuminate\Support\Str::random(12));
            }
        });
    }

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
