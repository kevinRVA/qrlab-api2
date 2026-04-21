<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasApiTokens;

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->user_code)) {
                $prefix = 'USER';
                switch ($user->role) {
                    case 'coordinador':
                        $prefix = 'CORD';
                        break;
                    case 'admin':
                    case 'administrador':
                        $prefix = 'ADMN';
                        break;
                    case 'profesor':
                        $prefix = 'PROF';
                        break;
                    case 'alumno':
                        $prefix = 'ALUM';
                        break;
                }
                
                $nextId = (\App\Models\User::max('id') ?? 0) + 1;
                $user->user_code = $prefix . '-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // <-- NUEVO
        'user_code',  // <-- NUEVO
        'career',     // <-- NUEVO
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Relación: Laboratorios asignados a un coordinador ──────────────────
    public function coordinatorLabs()
    {
        return $this->belongsToMany(Laboratory::class, 'coordinator_labs');
    }

    /** ¿Es este usuario coordinador de laboratorio? */
    public function isCoordinator(): bool
    {
        return $this->role === 'coordinador';
    }

    /** IDs numéricos de los labs asignados (para filtrar lab_visits, etc.) */
    public function getAssignedLabIds(): array
    {
        return $this->coordinatorLabs()->pluck('laboratories.id')->toArray();
    }

    /** Nombres de los labs asignados (para filtrar sessions.laboratory_name) */
    public function getAssignedLabNames(): array
    {
        return $this->coordinatorLabs()->pluck('laboratories.name')->toArray();
    }
}
