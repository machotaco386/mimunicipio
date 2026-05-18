<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function marcarLeida($id)
    {
        $notificacion = auth()->user()->notifications()->findOrFail($id);
        $notificacion->markAsRead();

        // Redirige a la URL que trae la notificación (ej. al tablero o a métricas)
        return redirect($notificacion->data['url']);
    }

    public function limpiarTodas()
    {
        // Marca todas como leídas (las oculta del contador)
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Bandeja de notificaciones limpia.');
    }
}
