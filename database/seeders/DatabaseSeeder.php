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
        // Limpiamos un poco los datos para no duplicar si se ejecuta varias veces
        // (Opcional, pero asumo que es base de datos de pruebas)

        // 1. Crear Grupos
        $grupoArtilleria = Grupo::create(['nombre' => 'Grupo de Artillería 1', 'descripcion' => 'Batería principal de largo alcance.']);
        $grupoMorteros = Grupo::create(['nombre' => 'Grupo de Morteros Pesados', 'descripcion' => 'Soporte cercano y de asedio.']);
        $grupoDrones = Grupo::create(['nombre' => 'Escuadrón Drones UAV', 'descripcion' => 'Impactos precisos teledirigidos.']);

        // 2. Crear Áreas y Vértices
        $areaAlfa = Area::create(['nombre' => 'Área Alfa (Centro de Mando)', 'x_objetivo' => 40.4168, 'y_objetivo' => -3.7038]);
        $areaBravo = Area::create(['nombre' => 'Área Bravo (Trincheras Este)', 'x_objetivo' => 40.4200, 'y_objetivo' => -3.6500]);
        $areaCharlie = Area::create(['nombre' => 'Área Charlie (Valle Norte)', 'x_objetivo' => 40.4500, 'y_objetivo' => -3.7200]);

        // Vertices para Área Alfa (Cuadrado imaginario)
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4150, 'y' => -3.7050]);
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4180, 'y' => -3.7050]);
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4180, 'y' => -3.7020]);
        Vertice::create(['id_area' => $areaAlfa->id, 'x' => 40.4150, 'y' => -3.7020]);

        // Vertices para Área Bravo
        Vertice::create(['id_area' => $areaBravo->id, 'x' => 40.4190, 'y' => -3.6520]);
        Vertice::create(['id_area' => $areaBravo->id, 'x' => 40.4210, 'y' => -3.6520]);
        Vertice::create(['id_area' => $areaBravo->id, 'x' => 40.4210, 'y' => -3.6480]);

        // 3. Crear Armas
        $arma1 = Arma::create(['tipo' => 'Obús 155mm', 'nombre' => 'M777 A2', 'descripcion' => 'Disparos balísticos de largo alcance', 'cord_x' => 40.3500, 'cord_y' => -3.8000, 'id_grupo' => $grupoArtilleria->id]);
        $arma2 = Arma::create(['tipo' => 'Lanzacohetes', 'nombre' => 'HIMARS', 'descripcion' => 'Disparos en racimo de precisión', 'cord_x' => 40.3520, 'cord_y' => -3.8100, 'id_grupo' => $grupoArtilleria->id]);
        $arma3 = Arma::create(['tipo' => 'Mortero 120mm', 'nombre' => 'M120', 'descripcion' => 'Mortero pesado', 'cord_x' => 40.4000, 'cord_y' => -3.6000, 'id_grupo' => $grupoMorteros->id]);
        $arma4 = Arma::create(['tipo' => 'Dron Kamikaze', 'nombre' => 'Switchblade 600', 'descripcion' => 'Dron merodeador', 'cord_x' => 40.3800, 'cord_y' => -3.6800, 'id_grupo' => $grupoDrones->id]);

        // 4. Crear Impactos
        // Impactos recientes de prueba simulados alrededor del Área Alfa y Bravo
        for ($i = 0; $i < 5; $i++) {
            Impacto::create([
                'id_area' => $areaAlfa->id,
                'id_arma' => $arma1->id,
                'x_impacto' => $areaAlfa->x_objetivo + (mt_rand(-100, 100) / 10000), // Random offset dispersión
                'y_impacto' => $areaAlfa->y_objetivo + (mt_rand(-100, 100) / 10000),
                'momento_impacto' => Carbon::now()->subMinutes(rand(10, 300)),
                'eficaz' => rand(0, 1) === 1
            ]);
        }
        for ($i = 0; $i < 3; $i++) {
            Impacto::create([
                'id_area' => $areaBravo->id,
                'id_arma' => $arma3->id,
                'x_impacto' => $areaBravo->x_objetivo + (mt_rand(-50, 50) / 10000), 
                'y_impacto' => $areaBravo->y_objetivo + (mt_rand(-50, 50) / 10000),
                'momento_impacto' => Carbon::now()->subHours(rand(1, 48)),
                'eficaz' => true
            ]);
        }
    }
}
