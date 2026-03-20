<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

#[Signature('app:iniciar')]
#[Description('Command description')]
class iniciar extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando servidor multicanal...');

        $servicioPhp = Process::timeout(0)->start('php artisan serve');
        $reverb = Process::timeout(0)->start('php artisan reverb:start');
        $queue  = Process::timeout(0)->start('php artisan queue:work');
        $vite   = Process::timeout(0)->start('npm run dev');

        $this->info('Servicios corriendo...');

        while ($reverb->running() && $servicioPhp->running() && $queue->running() && $vite->running()) {
            sleep(1);
        }
    }
}
