<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AlertaSistemaNotificacion extends Notification
{
    use Queueable;

    protected $titulo;
    protected $mensaje;

    public function __construct($titulo, $mensaje)
    {
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'folio' => null,
            'url' => route('admin.metricas.index'),
            'icono' => 'ph-chart-pie-slice',
            'color' => 'text-amber-500 bg-amber-50'
        ];
    }
}