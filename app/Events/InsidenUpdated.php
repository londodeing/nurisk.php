<?php

namespace App\Events;

use App\Models\OperasiInsiden;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InsidenUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $insiden;

    public function __construct(OperasiInsiden $insiden)
    {
        $this->insiden = $insiden;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('insiden.' . $this->insiden->id_insiden);
    }

    public function broadcastWith()
    {
        return [
            'id_insiden' => $this->insiden->id_insiden,
            'status_insiden' => $this->insiden->status_insiden,
            'waktu_mulai' => $this->insiden->waktu_mulai,
            'is_locked' => $this->insiden->is_locked,
        ];
    }
}
