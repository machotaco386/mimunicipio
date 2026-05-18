<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Area extends Model
{
    protected $table = 'areas';

    protected $fillable = ['municipio_id', 'nombre', 'color'];

    public function municipio(): BelongsTo { return $this->belongsTo(Municipio::class); }
    public function usuarios(): HasMany { return $this->hasMany(User::class); }
    public function cuadrillas(): HasMany { return $this->hasMany(Cuadrilla::class); }
    public function reportes(): HasMany { return $this->hasMany(Reporte::class); }
}