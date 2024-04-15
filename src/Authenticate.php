<?php

namespace LLoadout\Microsoftgraph;

use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use LLoadout\Microsoftgraph\EventListeners\MicrosoftGraphErrorReceived;
use LLoadout\Microsoftgraph\Traits\Authenticate as AuthTrait;

class Authenticate
{
    use AuthTrait;

    public function connect()
    {
        return Socialite::driver('microsoft')->with(['scope' => '.default', 'prompt' => 'consent'])->redirect();
    }

    public function callback()
    {
        if (request()->has('error')) {
            MicrosoftGraphErrorReceived::dispatch(encrypt((object)['error' => request('error'), 'error_description' => request('error_description')]));
        } else {
            $tokenData = Http::asForm()->post('https://login.microsoftonline.com/' . config('services.microsoft.tenant') . '/oauth2/token', $this->getTokenFields(request('code')))->object();
            $this->dispatchCallbackReceived($tokenData);
            return redirect('/');
        }
    }
}
