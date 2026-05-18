<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Reporte extends Model
{
    protected $table = 'reportes';

    protected $fillable = [
        'folio', 'municipio_id', 'area_id', 'cuadrilla_id', 'latitud', 'longitud', 
        'descripcion', 'categoria', 'ruta_foto', 'estado', 'telefono_contacto'
    ];

    protected static function booted(): void
    {
        static::creating(function (Reporte $reporte) {
            
            // 1. GENERACIÓN DE FOLIO
            $anio = date('Y');
            $prefijo = "MX-{$anio}-";

            DB::transaction(function () use ($reporte, $prefijo) {
                $ultimoReporte = self::where('folio', 'like', "{$prefijo}%")->lockForUpdate()->orderByDesc('id')->first();
                $siguienteNumero = $ultimoReporte ? ((int) substr($ultimoReporte->folio, -5)) + 1 : 1;
                $reporte->folio = $prefijo . str_pad($siguienteNumero, 5, '0', STR_PAD_LEFT);
            });

            // 2. AUTO-ENRUTAMIENTO INTELIGENTE A DEPARTAMENTOS
            if ($reporte->municipio_id) {
                $nombreArea = match ($reporte->categoria) {
                    'Fuga de agua', 'Drenaje' => 'Agua Potable',
                    'Bache', 'Pavimentación' => 'Obras Públicas',
                    'Luz', 'Alumbrado' => 'Alumbrado Público',
                    'Basura', 'Limpieza' => 'Servicios Generales',
                    default => 'Obras Públicas',
                };

                $area = Area::where('municipio_id', $reporte->municipio_id)->where('nombre', $nombreArea)->first();
                if ($area) {
                    $reporte->area_id = $area->id;
                }
            }
        });
    }

    public function municipio(): BelongsTo { return $this->belongsTo(Municipio::class); }
    public function area(): BelongsTo { return $this->belongsTo(Area::class); }
    public function cuadrilla(): BelongsTo { return $this->belongsTo(Cuadrilla::class); }
}