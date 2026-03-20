<?php

namespace App\Http\Controllers;

use App\Models\Vertice;
use Exception;
use Illuminate\Http\Request;

class VerticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $vertices = Vertice::all();
            $vertices_formateados = $vertices->map(function($vertice){
                return [
                    "id" => $vertice->id,
                    "x_vertice" => $vertice->x_vertice,
                    "y_vertice" => $vertice->y_vertice,
                    "area" => $vertice->area->nombre
                ];
            });
            return response()->json($vertices_formateados);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener los vertices",
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
            $vertice = new Vertice();
            $vertice->x_vertice = $request->x_vertice;
            $vertice->y_vertice = $request->y_vertice;
            $vertice->id_area = $request->id_area;
            $vertice->save();
            return response()->json([
                "message" => "Vertice creado exitosamente",
                "vertice" => $vertice
            ], 201);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al crear el vertice",
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
            $vertice = Vertice::find($id);
            $vertice_formateado = [
                "id" => $vertice->id,
                "x_vertice" => $vertice->x_vertice,
                "y_vertice" => $vertice->y_vertice,
                "area" => $vertice->area->nombre
            ];
            return response()->json($vertice_formateado);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener el vertice",
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
            $vertice = Vertice::find($id);
            $vertice->x_vertice = $request->x_vertice;
            $vertice->y_vertice = $request->y_vertice;
            $vertice->id_area = $request->id_area;
            $vertice->save();
            return response()->json([
                "message" => "Vertice actualizado exitosamente",
                "vertice" => $vertice
            ], 200);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al actualizar el vertice",
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
            $vertice = Vertice::find($id);
            $vertice->delete();
            return response()->json([
                "message" => "Vertice eliminado exitosamente"
            ], 200);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al eliminar el vertice",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
