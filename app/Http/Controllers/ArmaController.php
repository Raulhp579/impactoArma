<?php

namespace App\Http\Controllers;

use App\Models\Arma;
use Exception;
use Illuminate\Http\Request;

class ArmaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $armas = Arma::all();
            $armaFormateado = [];
            foreach($armas as $arma){
                $armaFormateado[] = [
                    "id" => $arma->id,
                    "nombre" => $arma->nombre,
                    "descripcion" => $arma->descripcion,
                    "grupo"=>$arma->grupo->nombre,
                    "x"=>$arma->cord_x,
                    "y"=>$arma->cord_y,
                    "tipo"=>$arma->tipo
                ];
            }
            return response()->json($armaFormateado);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener las armas",
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
            $arma = new Arma();
            $arma->nombre = $request->nombre;
            $arma->descripcion = $request->descripcion;
            $arma->id_grupo = $request->id_grupo;
            $arma->cord_x = $request->cord_x;
            $arma->cord_y = $request->cord_y;
            $arma->tipo = $request->tipo;
            $arma->save();
            return response()->json($arma);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al crear el arma",
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
            $arma = Arma::find($id);
            return response()->json([
                "id" => $arma->id,
                "nombre" => $arma->nombre,
                "descripcion" => $arma->descripcion,
                "grupo"=>$arma->grupo->nombre,
                "x"=>$arma->cord_x,
                "y"=>$arma->cord_y,
                "tipo"=>$arma->tipo
            ]);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener el arma",
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
            $arma = Arma::find($id);
            $arma->nombre = $request->nombre;
            $arma->descripcion = $request->descripcion;
            $arma->id_grupo = $request->id_grupo;
            $arma->cord_x = $request->cord_x;
            $arma->cord_y = $request->cord_y;
            $arma->tipo = $request->tipo;
            $arma->save();
            return response()->json($arma);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al actualizar el arma",
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
            $arma = Arma::find($id);
            $arma->delete();
            return response()->json($arma);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al eliminar el arma",
                "error" => $e->getMessage()
            ], 500);
        }
    }
}
