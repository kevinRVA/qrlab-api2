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
}
