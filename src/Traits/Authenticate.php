<?php

namespace LLoadout\Microsoftgraph\Traits;

use Laravel\Socialite\Facades\Socialite;
use LLoadout\Microsoftgraph\EventListeners\MicrosoftGraphCallbackReceived;

trait Authenticate
{
    public function connect()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback(): void
    {
        $user = (array) Socialite::driver('microsoft')->user();
        MicrosoftGraphCallbackReceived::dispatch($user);
    }

    public function getAccessToken()
    {
        return session('token');
    }
}
