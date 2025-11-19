<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportProductsFromApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
   protected $signature = 'products:import {--page=1 : Başlanacak sayfa numarası}';
// VEYA sadece


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
