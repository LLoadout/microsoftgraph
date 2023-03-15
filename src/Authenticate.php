<?php

namespace LLoadout\Microsoftgraph;

use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use LLoadout\Microsoftgraph\Traits\Authenticate as AuthTrait;

class Authenticate
{
    use AuthTrait;

    public function connect()
    {
        return Socialite::driver('microsoft')->with(['prompt' => 'consent'])->redirect();
    }

    public function callback(): void
    {
        $tokenData = Http::asForm()->post('https://login.microsoftonline.com/'.config('services.microsoft.tenant_id').'/oauth2/token', $this->getTokenFields(request('code')))->object();
        $this->dispatchCallbackReceived($tokenData);
    }
}
