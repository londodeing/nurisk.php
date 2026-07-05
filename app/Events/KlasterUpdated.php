<?php

namespace App\Events;

use App\Models\OperasiKlaster;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KlasterUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $klaster;

    public function __construct(OperasiKlaster $klaster)
    {
        $this->klaster = $klaster;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('klaster.' . $this->klaster->id_klaster_operasi);
    }

    public function broadcastWith()
    {
        return [
            'id_klaster_operasi' => $this->klaster->id_klaster_operasi,
            'id_insiden' => $this->klaster->id_insiden,
            'status_klaster' => $this->klaster->status_klaster,
            'progres_persen' => $this->klaster->progres_persen,
        ];
    }
}
