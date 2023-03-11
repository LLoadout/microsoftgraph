<?php

namespace LLoadout\Microsoftgraph\EventListeners;

use Illuminate\Foundation\Events\Dispatchable;

class MicrosoftGraphCallbackReceived
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public $user)
    {
    }
}
