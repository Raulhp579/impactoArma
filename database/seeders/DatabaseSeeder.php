<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Grupo;
use App\Models\Area;
use App\Models\Vertice;
use App\Models\Arma;
use App\Models\Impacto;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear Grupos
        $grupoArtilleria = Grupo::create(['nombre' => 'Grupo de Artilleria 1', 'descripcion' => 'Bateria principal de largo alcance.']);
        $grupoMorteros = Grupo::create(['nombre' => 'Grupo de Morteros Pesados', 'descripcion' => 'Soporte cercano y de asedio.']);
        $grupoDrones = Grupo::create(['nombre' => 'Escuadron Drones UAV', 'descripcion' => 'Impactos precisos teledirigidos.']);

        // 2. Crear Areas
        $areaAlfa = Area::create(['nombre' => 'Area Alfa (Centro de Mando)', 'x_objetivo' => 40.4168, 'y_objetivo' => -3.7038]);
        $areaBravo = Area::create(['nombre' => 'Area Bravo (Trincheras Este)', 'x_objetivo' => 40.4200, 'y_objetivo' => -3.6500]);
        $areaCharlie = Area::create(['nombre' => 'Area Charlie (Valle Norte)', 'x_objetivo' => 40.4500, 'y_objetivo' => -3.7200]);

        // Vertices para Area Alfa (cuadrado ~300m de lado centrado en el objetivo)
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4150, 'y' => -3.7060]);
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4185, 'y' => -3.7060]);
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4185, 'y' => -3.7010]);
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4150, 'y' => -3.7010]);

        // Vertices para Area Bravo
        Vertice::create(['id_area' => $areaBravo->id, 'x' => 40.4185, 'y' => -3.6525]);
        Vertice::create(['id_area' => $areaBravo->id, 'x' => 40.4215, 'y' => -3.6525]);
        Vertice::create(['id_area' => $areaBravo->id, 'x' => 40.4215, 'y' => -3.6475]);
        Vertice::create(['id_area' => $areaBravo->id, 'x' => 40.4185, 'y' => -3.6475]);

        // 3. Crear Armas
        $arma1 = Arma::create(['tipo' => 'Obus 155mm', 'nombre' => 'M777 A2', 'descripcion' => 'Disparos balisticos de largo alcance', 'cord_x' => 40.3500, 'cord_y' => -3.8000, 'id_grupo' => $grupoArtilleria->id]);
        $arma2 = Arma::create(['tipo' => 'Lanzacohetes', 'nombre' => 'HIMARS', 'descripcion' => 'Disparos en racimo de precision', 'cord_x' => 40.3520, 'cord_y' => -3.8100, 'id_grupo' => $grupoArtilleria->id]);
        $arma3 = Arma::create(['tipo' => 'Mortero 120mm', 'nombre' => 'M120', 'descripcion' => 'Mortero pesado', 'cord_x' => 40.4000, 'cord_y' => -3.6000, 'id_grupo' => $grupoMorteros->id]);
        $arma4 = Arma::create(['tipo' => 'Dron Kamikaze', 'nombre' => 'Switchblade 600', 'descripcion' => 'Dron merodeador', 'cord_x' => 40.3800, 'cord_y' => -3.6800, 'id_grupo' => $grupoDrones->id]);

        // 4. Crear Impactos con efectivo y eficacia CALCULADOS (no random)
        $verticesAlfa = $areaAlfa->vertices;
        $verticesBravo = $areaBravo->vertices;

        // 5 impactos en Area Alfa con distintas desviaciones
        for ($i = 0; $i < 5; $i++) {
            $x = $areaAlfa->x_objetivo + (mt_rand(-25, 25) / 10000); // offset max ~250m
            $y = $areaAlfa->y_objetivo + (mt_rand(-25, 25) / 10000);

            $efectivo = $this->isInsidePolygonSeeder($verticesAlfa, $x, $y);
            $eficacia = $this->calcEficaciaSeeder($areaAlfa->x_objetivo, $areaAlfa->y_objetivo, $x, $y);

            Impacto::create([
                'id_area' => $areaAlfa->id,
                'id_arma' => $arma1->id,
                'x_impacto' => $x,
                'y_impacto' => $y,
                'momento_impacto' => Carbon::now()->subMinutes(rand(10, 300)),
                'efectivo' => $efectivo,
                'eficacia' => $eficacia
            ]);
        }

        // 3 impactos en Area Bravo
        for ($i = 0; $i < 3; $i++) {
            $x = $areaBravo->x_objetivo + (mt_rand(-20, 20) / 10000);
            $y = $areaBravo->y_objetivo + (mt_rand(-20, 20) / 10000);

            $efectivo = $this->isInsidePolygonSeeder($verticesBravo, $x, $y);
            $eficacia = $this->calcEficaciaSeeder($areaBravo->x_objetivo, $areaBravo->y_objetivo, $x, $y);

            Impacto::create([
                'id_area' => $areaBravo->id,
                'id_arma' => $arma3->id,
                'x_impacto' => $x,
                'y_impacto' => $y,
                'momento_impacto' => Carbon::now()->subHours(rand(1, 48)),
                'efectivo' => $efectivo,
                'eficacia' => $eficacia
            ]);
        }
    }

    /**
     * Ray Casting para verificar si un punto lat/lon esta dentro de un poligono de vertices.
     * Los vertices usan x=lat, y=lon.
     */
    private function isInsidePolygonSeeder($vertices, float $lat, float $lon): bool
    {
        $count = count($vertices);
        if ($count < 3) return false;

        $inside = false;
        $j = $count - 1;
        for ($i = 0; $i < $count; $i++) {
            $vi_lat = $vertices[$i]->x;
            $vi_lon = $vertices[$i]->y;
            $vj_lat = $vertices[$j]->x;
            $vj_lon = $vertices[$j]->y;

            if (($vi_lon > $lon) != ($vj_lon > $lon) &&
                ($lat < ($vj_lat - $vi_lat) * ($lon - $vi_lon) / ($vj_lon - $vi_lon) + $vi_lat)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        return $inside;
    }

    /**
     * Calcula la eficacia basandose en la distancia Haversine al objetivo.
     * 100% = exactamente en el objetivo, 0% = a 100 metros o mas.
     */
    private function calcEficaciaSeeder(float $targetLat, float $targetLon, float $impLat, float $impLon): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($impLat - $targetLat);
        $dLon = deg2rad($impLon - $targetLon);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($targetLat)) * cos(deg2rad($impLat)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $dist = $earthRadius * $c;

        $radio = 100; // metros
        if ($dist <= $radio) {
            return max(0.0, min(100.0, round(100 * (1 - $dist / $radio), 2)));
        }
        return 0.0;
    }
}
