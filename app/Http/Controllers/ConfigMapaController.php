<?php

namespace App\Http\Controllers;

use App\Models\ConfigMapa;
use Exception;
use Illuminate\Http\Request;

class ConfigMapaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $configs = ConfigMapa::all();
            return response()->json($configs);
        } catch (Exception $e) {
            return response()->json([
                "message" => "Error al obtener la configuración del mapa",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $config = new ConfigMapa();
            $config->huso = $request->huso;
            $config->sistemaCoordenadas = $request->sistemaCoordenadas;
            $config->hemisferio = $request->hemisferio;
            $config->prefijo_nombre_boca = $request->prefijo_nombre_boca;
            $config->numero_boca_inicial = $request->numero_boca_inicial;
            $config->save();
            return response()->json($config, 201);
        } catch (Exception $e) {
            return response()->json([
                "message" => "Error al crear la configuración del mapa",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $config = ConfigMapa::find($id);
            if (!$config) {
                return response()->json(["message" => "Configuración no encontrada"], 404);
            }
            return response()->json($config);
        } catch (Exception $e) {
            return response()->json([
                "message" => "Error al obtener la configuración del mapa",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $config = ConfigMapa::find($id);
            if (!$config) {
                return response()->json(["message" => "Configuración no encontrada"], 404);
            }
            $config->huso = $request->huso;
            $config->sistemaCoordenadas = $request->sistemaCoordenadas;
            $config->hemisferio = $request->hemisferio;
            $config->prefijo_nombre_boca = $request->prefijo_nombre_boca;
            $config->numero_boca_inicial = $request->numero_boca_inicial;
            $config->save();
            return response()->json($config);
        } catch (Exception $e) {
            return response()->json([
                "message" => "Error al actualizar la configuración del mapa",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $config = ConfigMapa::find($id);
            if (!$config) {
                return response()->json(["message" => "Configuración no encontrada"], 404);
            }
            $config->delete();
            return response()->json($config);
        } catch (Exception $e) {
            return response()->json([
                "message" => "Error al eliminar la configuración del mapa",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
