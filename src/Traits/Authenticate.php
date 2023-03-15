<?php

namespace LLoadout\Microsoftgraph\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use LLoadout\Microsoftgraph\EventListeners\MicrosoftGraphCallbackReceived;

trait Authenticate
{
    public function refreshAccessToken($refreshtoken): void
    {
        $tokenData = Http::asForm()->post('https://login.microsoftonline.com/'.config('services.microsoft.tenant_id').'/oauth2/token', $this->getRefreshFields($refreshtoken))->object();
        $this->dispatchCallbackReceived($tokenData);
    }

    private function dispatchCallbackReceived($tokenData): void
    {
        $user = Http::withToken($tokenData->access_token)->get('https://graph.microsoft.com/v1.0/me')->object();
        MicrosoftGraphCallbackReceived::dispatch(encrypt((object) ['user' => $user, 'expires_on' => $tokenData->expires_on, 'access_token' => $tokenData->access_token, 'refresh_token' => $tokenData->refresh_token]));
    }

    public function getAccessToken()
    {
        if (! session()->has('microsoftgraph-access-data')) {
            throw new \Exception('Please create a session variable named microsoftgraph-access-data with your access data as value');
        }
        $accessData = decrypt(session('microsoftgraph-access-data'));
        if (isset($accessData->access_token)) {
            if (Carbon::createFromTimestamp($accessData->expires_on)->lte(Carbon::now())) {
                $this->refreshAccessToken($accessData->refresh_token);
            }

            return $accessData->access_token;
        }
        throw new \Exception('Your access data is invalid, please reconnect');
    }

    private function getBaseFields()
    {
        $base_args = [];
        $base_args = Arr::add($base_args, 'client_id', config('services.microsoft.client_id'));
        $base_args = Arr::add($base_args, 'client_secret', config('services.microsoft.client_secret'));
        $base_args = Arr::add($base_args, 'redirect_uri', config('services.microsoft.redirect'));

        return $base_args;
    }

    protected function getRefreshFields($refresh)
    {
        $base_args = $this->getBaseFields();
        $base_args = Arr::add($base_args, 'grant_type', 'refresh_token');
        $base_args = Arr::add($base_args, 'scope', 'openid profile offline_access');
        $base_args = Arr::add($base_args, 'refresh_token', $refresh);

        return $base_args;
    }

    protected function getTokenFields($code = null)
    {
        $base_args = $this->getBaseFields();
        $base_args = Arr::add($base_args, 'grant_type', 'authorization_code');
        $base_args = Arr::add($base_args, 'code', $code);
        $base_args = Arr::add($base_args, 'resource', 'https://graph.microsoft.com');

        return $base_args;
    }
}
