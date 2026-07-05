<?php

namespace App\Events\Operasi;

use App\Models\OperasiPleno;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlenoFinalized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $pleno;

    /**
     * Create a new event instance.
     */
    public function __construct(OperasiPleno $pleno)
    {
        $this->pleno = $pleno;
    }
}
