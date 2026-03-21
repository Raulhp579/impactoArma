<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Vertice;
use Exception;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $areas = Area::with(['vertices', 'objetivos'])->get();
            return response()->json($areas);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener las areas",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $area = new Area();
            $area->nombre = $request->nombre;
            // Prevenir error NOT NULL constraint si las columnas antiguas siguen en la DB
            $area->x_objetivo = 0; 
            $area->y_objetivo = 0;
            
            $area->save();
            if ($request->has('objetivos')) {
                foreach ($request->objetivos as $v) {
                    \App\Models\ObjetivoArea::create([
                        'id_area' => $area->id,
                        'x_zona' => $v['x_zona'],
                        'y_zona' => $v['y_zona']
                    ]);
                }
            }
            if ($request->has('vertices')) {
                foreach ($request->vertices as $v) {
                    Vertice::create([
                        'id_area' => $area->id,
                        'x' => $v['x'],
                        'y' => $v['y']
                    ]);
                }
            }
            return response()->json($area->load('vertices'));
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al crear el area",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $area = Area::with(['vertices', 'objetivos'])->find($id);
            return response()->json($area);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener el area",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
            $area = Area::find($id);
            $area->nombre = $request->nombre;
            // Prevenir error NOT NULL constraint si las columnas antiguas siguen en la DB
            $area->x_objetivo = 0; 
            $area->y_objetivo = 0;
            $area->save();
            if ($request->has('objetivos')) {
                \App\Models\ObjetivoArea::where('id_area', $area->id)->delete();
                foreach ($request->objetivos as $v) {
                    \App\Models\ObjetivoArea::create([
                        'id_area' => $area->id,
                        'x_zona' => $v['x_zona'],
                        'y_zona' => $v['y_zona']
                    ]);
                }
            }
            if ($request->has('vertices')) {
                Vertice::where('id_area', $area->id)->delete();
                foreach ($request->vertices as $v) {
                    Vertice::create([
                        'id_area' => $area->id,
                        'x' => $v['x'],
                        'y' => $v['y']
                    ]);
                }
            }
            return response()->json($area->load('vertices'));
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al actualizar el area",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $area = Area::find($id);
            $area->delete();
            return response()->json($area);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al eliminar el area",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
