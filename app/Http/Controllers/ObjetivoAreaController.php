<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ObjetivoArea;
use Exception;

class ObjetivoAreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            return response()->json(ObjetivoArea::all());
        } catch (Exception $e) {
            return response()->json(["message" => "Error al obtener objetivos", "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $obj = ObjetivoArea::create($request->all());
            return response()->json($obj, 201);
        } catch (Exception $e) {
            return response()->json(["message" => "Error al crear objetivo", "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            return response()->json(ObjetivoArea::find($id));
        } catch (Exception $e) {
            return response()->json(["message" => "Error al obtener objetivo", "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $obj = ObjetivoArea::find($id);
            if (!$obj) return response()->json(["message" => "No encontrado"], 404);
            $obj->update($request->all());
            return response()->json($obj);
        } catch (Exception $e) {
            return response()->json(["message" => "Error al actualizar objetivo", "error" => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $obj = ObjetivoArea::find($id);
            if (!$obj) return response()->json(["message" => "No encontrado"], 404);
            $obj->delete();
            return response()->json($obj);
        } catch (Exception $e) {
            return response()->json(["message" => "Error al eliminar objetivo", "error" => $e->getMessage()], 500);
        }
    }
}
