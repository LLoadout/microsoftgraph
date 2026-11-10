<?php

namespace LLoadout\Microsoftgraph;

use Laravel\Socialite\Facades\Socialite;
use LLoadout\Microsoftgraph\Traits\Authenticate as AuthTrait;

/**
 * Class Authenticate
 *
 * Handles Microsoft Graph API authentication using OAuth2 flow
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
     * - Dispatches error event if authentication failed (error from Microsoft, no code,
     *   or a code that could not be exchanged for an access token)
     * - Retrieves access token and dispatches success event if authentication succeeded
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function callback()
    {
        if (request()->has('error')) {
            $this->dispatchErrorReceived(request('error'), request('error_description'));

            return;
        }

        if ($this->handleAuthorizationCode(request('code'))) {
            return redirect(config('services.microsoft.redirect_after_callback'));
        }
    }
}
