<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Exception;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $grupos = Grupo::all();
            return response()->json($grupos);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener los grupos",
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
            $grupo = new Grupo();
            $grupo->nombre = $request->nombre;
            $grupo->descripcion = $request->descripcion;
            $grupo->save();
            return response()->json($grupo);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al crear el grupo",
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
            $grupo = Grupo::find($id);
            return response()->json($grupo);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener el grupo",
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
            $grupo = Grupo::find($id);
            $grupo->nombre = $request->nombre;
            $grupo->descripcion = $request->descripcion;
            $grupo->save();
            return response()->json($grupo);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al actualizar el grupo",
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
            $grupo = Grupo::find($id);
            $grupo->delete();
            return response()->json($grupo);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al eliminar el grupo",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
