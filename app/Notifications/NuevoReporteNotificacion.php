<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevoReporteNotificacion extends Notification
{
    use Queueable;

    protected $reporte;

    public function __construct($reporte)
    {
        $this->reporte = $reporte;
    }

    public function via($notifiable)
    {
        return ['database']; // Solo guardamos en BD para el MVP
    }

    public function toDatabase($notifiable)
    {
        return [
            'titulo' => 'Nuevo Reporte Ciudadano',
            'mensaje' => 'Se ha ingresado un nuevo reporte: ' . $this->reporte->categoria,
            'folio' => $this->reporte->folio,
            'url' => route('admin.reportes.index'),
            'icono' => 'ph-file-plus',
            'color' => 'text-blue-500 bg-blue-50'
        ];
    }
}
