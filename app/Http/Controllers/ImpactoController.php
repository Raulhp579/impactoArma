<?php

namespace App\Http\Controllers;

use App\Events\ImpactoFallido;
use App\Models\Impacto;
use Exception;
use Illuminate\Http\Request;

class ImpactoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $impactos = Impacto::all();
            $impactos_formateados = $impactos->map(function($impacto){
                return [
                    "id" => $impacto->id,
                    "x_impacto" => $impacto->x_impacto,
                    "y_impacto" => $impacto->y_impacto,
                    "momento_impacto" => $impacto->momento_impacto,
                    "efectivo" => $impacto->efectivo,
                    "eficaz" => $impacto->eficaz,
                    "area" => $impacto->area->nombre,
                    "arma" => $impacto->arma->nombre
                ];
            });
            return response()->json($impactos_formateados);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener los impactos",
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
            $impacto = new Impacto();
            $impacto->x_impacto = $request->x_impacto;
            $impacto->y_impacto = $request->y_impacto;
            $impacto->momento_impacto = $request->momento_impacto;
            $impacto->efectivo = $request->efectivo;
            $impacto->eficaz = $request->eficaz;
            $impacto->id_area = $request->id_area;
            $impacto->id_arma = $request->id_arma;
            $impacto->save();
            if(!$impacto->eficaz){
                event(new ImpactoFallido($impacto));
            }
            return response()->json([
                "message" => "Impacto creado exitosamente",
                "impacto" => $impacto
            ], 201);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al crear el impacto",
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
            $impacto = Impacto::find($id);
            $impacto_formateado = [
                "id" => $impacto->id,
                "x_impacto" => $impacto->x_impacto,
                "y_impacto" => $impacto->y_impacto,
                "momento_impacto" => $impacto->momento_impacto,
                "efectivo" => $impacto->efectivo,
                "eficaz" => $impacto->eficaz,
                "area" => $impacto->area->nombre,
                "arma" => $impacto->arma->nombre
            ];
            return response()->json($impacto_formateado);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener el impacto",
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
            $impacto = Impacto::find($id);
            $impacto->x_impacto = $request->x_impacto;
            $impacto->y_impacto = $request->y_impacto;
            $impacto->momento_impacto = $request->momento_impacto;
            $impacto->eficaz = $request->eficaz;
            $impacto->id_area = $request->id_area;
            $impacto->id_arma = $request->id_arma;
            $impacto->save();
            return response()->json([
                "message" => "Impacto actualizado exitosamente",
                "impacto" => $impacto
            ], 200);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al actualizar el impacto",
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
            $impacto = Impacto::find($id);
            $impacto->delete();
            return response()->json([
                "message" => "Impacto eliminado exitosamente"
            ], 200);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al eliminar el impacto",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
