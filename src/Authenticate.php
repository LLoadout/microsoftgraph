<?php

namespace LLoadout\Microsoftgraph;

use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use LLoadout\Microsoftgraph\EventListeners\MicrosoftGraphErrorReceived;
use LLoadout\Microsoftgraph\Traits\Authenticate as AuthTrait;

/**
 * Class Authenticate
 *
 * Handles Microsoft Graph API authentication using OAuth2 flow
 *
 * @package LLoadout\Microsoftgraph
 */
class Authenticate
{
    use AuthTrait;

    /**
     * Initiates the OAuth2 authentication flow with Microsoft
     *
     * Redirects user to Microsoft login page with required scopes and consent prompt
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function connect()
    {
        return Socialite::driver('microsoft')->with(['scope' => '.default', 'prompt' => 'consent'])->redirect();
    }

    /**
     * Handles the OAuth2 callback from Microsoft
     *
     * Processes the authentication response, either:
     * - Dispatches error event if authentication failed
     * - Retrieves access token and dispatches success event if authentication succeeded
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function callback()
    {
        if (request()->has('error')) {
            MicrosoftGraphErrorReceived::dispatch(encrypt((object)['error' => request('error'), 'error_description' => request('error_description')]));
        } else {
            $tokenData = Http::asForm()->post('https://login.microsoftonline.com/' . config('services.microsoft.tenant') . '/oauth2/token', $this->getTokenFields(request('code')))->object();
            $this->dispatchCallbackReceived($tokenData);
            return redirect(config('services.microsoft.redirect_after_callback'));
        }
    }
}
