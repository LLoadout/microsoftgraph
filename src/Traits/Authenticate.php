<?php

namespace LLoadout\Microsoftgraph\Traits;


use http\Client\Request;
use Laravel\Socialite\Facades\Socialite;

trait Authenticate
{

    public function connect()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback(Request $request): void
    {
        $user = (array)Socialite::driver('microsoft')->user();
        $request->session()->put('token', $user['token']);
    }
}
