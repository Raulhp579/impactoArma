<?php

use App\Models\Area;
use App\Models\Impacto;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/gestionar-areas', function () {
    $areas = Area::all();

    return view('gestionarAreas', compact('areas'));
});

Route::get('/gestion-impactos', function () {
    $impactos = Impacto::all();

    return view('gestionImpactos', compact('impactos'));
});
