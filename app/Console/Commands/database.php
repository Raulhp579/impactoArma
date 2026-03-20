<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:database')]
#[Description('Command description')]
class database extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('borrando datos de la base de datos');
        $this->call('db:wipe');
        $this->info('cargando migraciones...');
        $this->call('migrate');
        $this->info('cargando seeders...');
        $this->call('db:seed');
        $this->info('migraciones y seeders cargados correctamente');
    }
}
