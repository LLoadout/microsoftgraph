<?php

namespace LLoadout\Microsoftgraph;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use LLoadout\Microsoftgraph\Traits\Mail;

class Microsoftgraph
{
    use Mail;

    public function connect()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback(Request $request): void
    {
        $user = (array) Socialite::driver('microsoft')->user();
        $request->session()->put('token', $user['token']);
    }

    public function getAccessToken()
    {
        return session('token');
    }
}
