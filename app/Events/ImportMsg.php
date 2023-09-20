<?php


namespace App\Events;


use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class ImportMsg implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $addedNumber;
    public $skippedNumber;

    public function __construct($addedNumber, $skippedNumber)
    {
        $this->addedNumber = $addedNumber;
        $this->skippedNumber = $skippedNumber;
    }

    public function broadcastAs(): string
    {
        return 'imported';
    }

    public function broadcastOn()
    {
        return new Channel('import');
    }
}

