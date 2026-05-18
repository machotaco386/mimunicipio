<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Municipio;
use App\Models\Area;
use App\Models\Cuadrilla;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Las coordenadas de estos municipios ahora se manejan dinámicamente
        // en el ReporteController, manteniendo la base de datos intacta.

        // =========================================================
        // 1. MUNICIPIO 1: MEXTICACÁN
        // =========================================================
        $mexticacan = Municipio::firstOrCreate(
            ['nombre' => 'Mexticacán']
        );

        // Áreas de Mexticacán
        $areaObrasMex = Area::firstOrCreate(['municipio_id' => $mexticacan->id, 'nombre' => 'Obras Públicas'], ['color' => '#f59e0b']);
        $areaAguaMex = Area::firstOrCreate(['municipio_id' => $mexticacan->id, 'nombre' => 'Agua Potable'], ['color' => '#3b82f6']);
        $areaLuzMex = Area::firstOrCreate(['municipio_id' => $mexticacan->id, 'nombre' => 'Alumbrado Público'], ['color' => '#eab308']);
        $areaBasuraMex = Area::firstOrCreate(['municipio_id' => $mexticacan->id, 'nombre' => 'Servicios Generales'], ['color' => '#84CC16']);

        // Cuadrillas de Mexticacán
        Cuadrilla::firstOrCreate(['area_id' => $areaObrasMex->id, 'nombre' => 'Unidad Bacheo 01', 'icono' => 'ph-truck']);
        Cuadrilla::firstOrCreate(['area_id' => $areaAguaMex->id, 'nombre' => 'Brigada Fugas A', 'icono' => 'ph-drop']);

        // Usuarios de Mexticacán
        User::firstOrCreate(['email' => 'admin@mimunicipio.com'], [
            'name' => 'Presidente Municipal (Mexticacán)', 
            'password' => Hash::make('admin123'),
            'rol' => 'super_admin', 
            'municipio_id' => $mexticacan->id
        ]);

        User::firstOrCreate(['email' => 'obras@mimunicipio.com'], [
            'name' => 'Ing. Roberto (Obras)', 
            'password' => Hash::make('admin123'),
            'rol' => 'coordinador', 
            'municipio_id' => $mexticacan->id,
            'area_id' => $areaObrasMex->id
        ]);


        // =========================================================
        // 2. MUNICIPIO 2: TEOCALTICHE
        // =========================================================
        $teocaltiche = Municipio::firstOrCreate(
            ['nombre' => 'Teocaltiche']
        );

        // Áreas de Teocaltiche
        $areaObrasTeo = Area::firstOrCreate(['municipio_id' => $teocaltiche->id, 'nombre' => 'Mantenimiento Vial'], ['color' => '#8b5cf6']);
        $areaLuzTeo = Area::firstOrCreate(['municipio_id' => $teocaltiche->id, 'nombre' => 'Iluminación Pública'], ['color' => '#eab308']);

        // Cuadrillas de Teocaltiche
        Cuadrilla::firstOrCreate(['area_id' => $areaObrasTeo->id, 'nombre' => 'Brigada Teoca 1', 'icono' => 'ph-hard-hat']);

        // Usuarios de Teocaltiche
        User::firstOrCreate(['email' => 'alcalde@teocaltiche.gob.mx'], [
            'name' => 'Presidente Municipal (Teocaltiche)', 
            'password' => Hash::make('admin123'),
            'rol' => 'super_admin', 
            'municipio_id' => $teocaltiche->id
        ]);


        // =========================================================
        // 3. MUNICIPIO 3: NOCHISTLÁN
        // =========================================================
        $nochistlan = Municipio::firstOrCreate(
            ['nombre' => 'Nochistlán']
        );

        // Áreas de Nochistlán
        $areaAguaNoch = Area::firstOrCreate(['municipio_id' => $nochistlan->id, 'nombre' => 'Sistema de Agua Potable'], ['color' => '#3b82f6']);
        $areaBasuraNoch = Area::firstOrCreate(['municipio_id' => $nochistlan->id, 'nombre' => 'Aseo Público'], ['color' => '#22c55e']);

        // Cuadrillas de Nochistlán
        Cuadrilla::firstOrCreate(['area_id' => $areaAguaNoch->id, 'nombre' => 'Unidad Fugas 01', 'icono' => 'ph-drop']);

        // Usuarios de Nochistlán
        User::firstOrCreate(['email' => 'presidencia@nochistlan.gob.mx'], [
            'name' => 'Presidente Municipal (Nochistlán)', 
            'password' => Hash::make('admin123'),
            'rol' => 'super_admin', 
            'municipio_id' => $nochistlan->id
        ]);

        // Un trabajador de campo genérico para probar la App Móvil (Asignado a Mexticacán)
        User::firstOrCreate(['email' => 'pedro@mimunicipio.com'], [
            'name' => 'Pedro (Trabajador)', 
            'password' => Hash::make('123456'),
            'rol' => 'trabajador', 
            'municipio_id' => $mexticacan->id,
            'area_id' => $areaObrasMex->id
        ]);
    }
}