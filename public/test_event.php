<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Events\ImpactoFallido;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$dummyImpacto = [
    "id" => 999123,
    "x_impacto" => 40.0,
    "y_impacto" => -3.0,
    "momento_impacto" => date('Y-m-d H:i:s'),
    "efectivo" => false,
    "eficacia" => 0,
    "id_area" => 1,
    "id_arma" => 1,
    "id_objetivo" => null
];

// Convert to object for event if needed, but array should be fine or cast
$impactoObj = (object)$dummyImpacto;

try {
    echo "Disparando evento ImpactoFallido...\n";
    event(new ImpactoFallido($impactoObj));
    echo "Evento disparado con éxito.\n";
} catch (\Exception $e) {
    echo "ERROR al disparar evento: " . $e->getMessage() . "\n";
}
