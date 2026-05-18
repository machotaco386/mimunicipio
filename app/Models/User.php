<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable
{
    // AGREGAR HasApiTokens aquí
    use HasApiTokens, HasFactory, Notifiable; 

    protected $fillable = ['name', 'email', 'password', 'municipio_id', 'area_id', 'rol'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }

    public function municipio(): BelongsTo { return $this->belongsTo(Municipio::class, 'municipio_id'); }
    public function area(): BelongsTo { return $this->belongsTo(Area::class, 'area_id'); }
    
    public function cuadrillas(): BelongsToMany
    {
        return $this->belongsToMany(Cuadrilla::class, 'cuadrilla_user', 'user_id', 'cuadrilla_id')
                    ->withPivot('fecha_asignacion')->withTimestamps();
    }
}