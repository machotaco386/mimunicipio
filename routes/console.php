<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\User;
use App\Notifications\AlertaSistemaNotificacion;

// Notificación de Fin de Mes (Se ejecuta el último día del mes a las 8:00 AM)
Schedule::call(function () {
    $admins = User::where('rol', 'admin')->get();
    Notification::send($admins, new AlertaSistemaNotificacion(
        'Cierre de Mes Listo', 
        'Las métricas y el reporte PDF de este mes ya están disponibles para su descarga.'
    ));
})->monthlyOn(date('t'), '08:00');

// Notificación Trimestral (Se ejecuta el primer día de cada trimestre)
Schedule::call(function () {
    $admins = User::where('rol', 'admin')->get();
    Notification::send($admins, new AlertaSistemaNotificacion(
        'Reporte Trimestral', 
        'Es momento de revisar los KPIs y la eficiencia operativa del último trimestre.'
    ));
})->quarterly();