<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cuadrilla extends Model
{
    protected $table = 'cuadrillas';

    // Agregamos 'icono' al array
    protected $fillable = ['area_id', 'nombre', 'icono', 'activa'];

    protected $casts = ['activa' => 'boolean'];

    public function area(): BelongsTo { return $this->belongsTo(Area::class); }
    
    public function trabajadores(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cuadrilla_user', 'cuadrilla_id', 'user_id')
                    ->withPivot('fecha_asignacion')->withTimestamps();
    }

    public function reportes(): HasMany { return $this->hasMany(Reporte::class); }
}