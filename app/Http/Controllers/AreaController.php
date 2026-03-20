<?php

namespace App\Http\Controllers;

use App\Models\Area;
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
            $areas = Area::all();
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
            $area->x_objetivo = $request->x_objetivo;
            $area->y_objetivo = $request->y_objetivo;
            $area->save();
            return response()->json($area);
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
            $area = Area::find($id);
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
            $area->x_objetivo = $request->x_objetivo;
            $area->y_objetivo = $request->y_objetivo;
            $area->save();
            return response()->json($area);
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
