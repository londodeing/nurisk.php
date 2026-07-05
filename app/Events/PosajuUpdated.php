<?php

namespace App\Events;

use App\Models\OperasiPosaju;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PosajuUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $posaju;

    public function __construct(OperasiPosaju $posaju)
    {
        $this->posaju = $posaju;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('posaju.' . $this->posaju->id_posaju);
    }

    public function broadcastWith()
    {
        return [
            'id_posaju' => $this->posaju->id_posaju,
            'id_insiden' => $this->posaju->id_insiden,
            'status_alur' => $this->posaju->status_alur,
        ];
    }
}
