<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateProbabilitasBencana extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nurisk:update-probabilitas {--semua} {--kab=}';

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
