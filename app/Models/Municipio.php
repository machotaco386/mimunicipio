<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipio extends Model
{
    protected $table = 'municipios';
    protected $fillable = ['nombre'];

    public function reportes(): HasMany
    {
        return $this->hasMany(Reporte::class, 'municipio_id');
    }

    // Usamos el modelo User de Laravel, pero lo llamamos 'usuarios' para nuestra lógica
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'municipio_id');
    }
}