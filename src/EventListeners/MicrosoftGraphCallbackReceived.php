<?php

namespace LLoadout\Microsoftgraph\EventListeners;

use Illuminate\Foundation\Events\Dispatchable;

class MicrosoftGraphCallbackReceived
{
    use Dispatchable;

    public function __construct(public $user)
    {
    }
}
