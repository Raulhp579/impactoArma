<?php

namespace App\Http\Controllers;

use App\Events\ImpactoFallido;
use App\Models\Area;
use App\Models\Vertice;
use App\Models\Impacto;
use Exception;
use Illuminate\Console\Scheduling\Event;
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
                    "efectivo" => (bool) $impacto->efectivo,
                    "eficacia" => $impacto->eficacia,
                    "area" => $impacto->area->nombre,
                    "arma" => $impacto->arma->nombre,
                    "id_objetivo" => $impacto->id_objetivo // <--- Nuevo
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
     * Display a listing of the resource with full details.
     */
    public function conDetalles()
    {
        try{
            $impactos = Impacto::with(['area', 'arma'])->get();
            $impactos_formateados = $impactos->map(function($impacto){
                return [
                    "id" => $impacto->id,
                    "x_impacto" => $impacto->x_impacto,
                    "y_impacto" => $impacto->y_impacto,
                    "momento_impacto" => $impacto->momento_impacto,
                    "efectivo" => (bool) $impacto->efectivo,
                    "eficacia" => $impacto->eficacia,
                    "id_area" => $impacto->id_area,
                    "area" => $impacto->area ? $impacto->area->nombre : null,
                    "id_arma" => $impacto->id_arma,
                    "arma" => $impacto->arma ? $impacto->arma->nombre : null,
                    "id_objetivo" => $impacto->id_objetivo
                ];
            });
            return response()->json($impactos_formateados);
        }catch(Exception $e){
            return response()->json([
                "message" => "Error al obtener los impactos con detalles",
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
            $impacto->id_area = $request->id_area;
            $impacto->id_objetivo = $request->id_objetivo; // <--- Nuevo

            $area = Area::with('objetivos')->where("id", $request->id_area)->first();
            if (!$area) {
                throw new Exception("Área no encontrada.");
            }

            // 1. Coordenadas del impacto ya estan en Lat/Lon
            $impLat = (float) $request->x_impacto;
            $impLon = (float) $request->y_impacto;

            // 2. Evaluar si es Efectivo (dentro del poligono)
            $vertices = Vertice::where("id_area", $area->id)->get();
            $impacto->efectivo = $this->isInsidePolygon($vertices, $impLat, $impLon);

            // 3. Calcular Eficacia (%) - Haversine en Lat/Lon (metros reales)
            // Solo tiene eficacia si tambien es efectivo
            if ($impacto->efectivo) {
                $distanciaMetros = null;

                if ($request->id_objetivo) {
                    $obj = \App\Models\ObjetivoArea::find($request->id_objetivo);
                    if ($obj) {
                        $distanciaMetros = $this->getHaversineDistance($obj->x_zona, $obj->y_zona, $impLat, $impLon);
                    }
                } else {
                    foreach ($area->objetivos as $obj) {
                        $d = $this->getHaversineDistance($obj->x_zona, $obj->y_zona, $impLat, $impLon);
                        if ($distanciaMetros === null || $d < $distanciaMetros) {
                            $distanciaMetros = $d;
                        }
                    }
                }

                if ($distanciaMetros === null) $distanciaMetros = 999999;
                $radioEficaciaM = 100;
                if ($distanciaMetros <= $radioEficaciaM) {
                    $porcentaje = 100 * (1 - ($distanciaMetros / $radioEficaciaM));
                    $impacto->eficacia = max(0, min(100, round($porcentaje, 2)));
                } else {
                    $impacto->eficacia = 0;
                }
            } else {
                $impacto->eficacia = 0; // Fallido siempre tiene eficacia 0
            }

            $impacto->id_arma = $request->id_arma;
            $impacto->save();
            if(!$impacto->efectivo){
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
                "efectivo" => (bool) $impacto->efectivo,
                "eficacia" => $impacto->eficacia,
                "area" => $impacto->area->nombre,
                "arma" => $impacto->arma->nombre,
                "id_objetivo" => $impacto->id_objetivo // <--- Nuevo
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
            $impacto->id_area = $request->id_area;
            $impacto->id_objetivo = $request->id_objetivo; // <--- Nuevo
            
            $area = Area::with('objetivos')->where("id", $request->id_area)->first();
            if ($area) {
                // 1. Coordenadas ya en Lat/Lon
                $impLat = (float) $request->x_impacto;
                $impLon = (float) $request->y_impacto;

                // 2. Evaluar si es Efectivo (dentro del poligono)
                $vertices = Vertice::where("id_area", $area->id)->get();
                $impacto->efectivo = $this->isInsidePolygon($vertices, $impLat, $impLon);

                // 3. Calcular Eficacia - solo si es efectivo
                if ($impacto->efectivo) {
                    $distanciaMetros = null;

                    if ($request->id_objetivo) {
                        $obj = \App\Models\ObjetivoArea::find($request->id_objetivo);
                        if ($obj) {
                            $distanciaMetros = $this->getHaversineDistance($obj->x_zona, $obj->y_zona, $impLat, $impLon);
                        }
                    } else {
                        foreach ($area->objetivos as $obj) {
                            $d = $this->getHaversineDistance($obj->x_zona, $obj->y_zona, $impLat, $impLon);
                            if ($distanciaMetros === null || $d < $distanciaMetros) {
                                $distanciaMetros = $d;
                            }
                        }
                    }

                    if ($distanciaMetros === null) $distanciaMetros = 999999;
                    $radioEficaciaM = 100;
                    if ($distanciaMetros <= $radioEficaciaM) {
                        $porcentaje = 100 * (1 - ($distanciaMetros / $radioEficaciaM));
                        $impacto->eficacia = max(0, min(100, round($porcentaje, 2)));
                    } else {
                        $impacto->eficacia = 0;
                    }
                } else {
                    $impacto->eficacia = 0;
                }
            }

            $impacto->id_arma = $request->id_arma;
            $impacto->save();

            Event(new ImpactoFallido($impacto));
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
    private function isInsidePolygon($vertices, $x, $y)
    {
        $inside = false;
        $count = count($vertices);
        if ($count < 3) return false; // Un polígono necesita mínimo 3 vértices

        $j = $count - 1;
        for ($i = 0; $i < $count; $i++) {
            // Algoritmo de Ray Casting (Punto en Polígono)
            if (($vertices[$i]->y > $y) != ($vertices[$j]->y > $y) &&
                ($x < ($vertices[$j]->x - $vertices[$i]->x) * ($y - $vertices[$i]->y) / ($vertices[$j]->y - $vertices[$i]->y) + $vertices[$i]->x)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        return $inside;
    }

    private function getEuclideanDistance($x1, $y1, $x2, $y2)
    {
        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
    }

    /**
     * Convierte coordenadas UTM zona 30N (ETRS89/WGS84) a Lat/Lon en grados
     * Asume zona 30N que es la usada para España/Europa occidental
     */
    private function utmToLatLon($easting, $northing, $zone = 30, $isNorth = true)
    {
        $k0 = 0.9996;
        $a = 6378137.0;
        $e = 0.00669438;
        $e2 = $e * $e;
        $e3 = $e2 * $e;
        $e_p2 = $e / (1.0 - $e);

        $d = ($easting - 500000) / ($k0 * $a);

        $phi1 = $northing / ($k0 * $a);
        if (!$isNorth) $phi1 -= 10000000.0 / ($k0 * $a);

        $n = $a / sqrt(1 - $e * sin($phi1) ** 2);
        $t = tan($phi1) ** 2;
        $c = $e_p2 * cos($phi1) ** 2;
        $r = $a * (1 - $e) / pow(1 - $e * sin($phi1) ** 2, 1.5);
        $ph = $phi1 - ($n * tan($phi1) / $r) * (
            $d**2/2 - (5 + 3*$t + 10*$c - 4*$c**2 - 9*$e_p2) * $d**4/24
            + (61 + 90*$t + 298*$c + 45*$t**2 - 252*$e_p2 - 3*$c**2) * $d**6/720
        );

        $lat = rad2deg($ph);
        $lon = rad2deg(
            ($d - (1 + 2*$t + $c) * $d**3/6
            + (5 - 2*$c + 28*$t - 3*$c**2 + 8*$e_p2 + 24*$t**2) * $d**5/120
            ) / cos($ph)
        ) + ($zone * 6 - 183);

        return [$lat, $lon];
    }

    private function getHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
