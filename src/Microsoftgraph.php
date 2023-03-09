<?php

namespace LLoadout\Microsoftgraph;

use Illuminate\Mail\Mailable;
use JetBrains\PhpStorm\NoReturn;
use Laravel\Socialite\Facades\Socialite;
use Microsoft\Graph\Graph;

class Microsoftgraph
{
    public function connect()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function callback()
    {
        $user = Socialite::driver('microsoft')->user();
        session(['token' => $user->token]);
    }

    #[NoReturn] public function sendMail(Mailable $mailable)
    {
        $graph = new Graph();
        $graph->setAccessToken(session('token'));

        $user = (object)$graph->createRequest('GET', '/me')->execute()->getBody();

        $envelope = $mailable->envelope();
        $to       = $envelope->to;
        $html     = $mailable->render();

        $mailBody = ['Message' => [
            'subject'      => 'Test Email',
            'body'         => [
                'contentType' => 'html',
                'content'     => $html,
            ],
            'sender'       => [
                'emailAddress' => [
                    'name'    => $user->displayName,
                    'address' => $user->mail,
                ],
            ],
            'from'         => [
                'emailAddress' => [
                    'name'    => $user->displayName,
                    'address' => $user->mail,
                ],
            ],
            'toRecipients' => [
                [
                    'emailAddress' => [
                        'name'    => $to['name']->address,
                        'address' => $to['address']->address,

                    ],
                ],
            ],
        ],
        ];

        $graph->createRequest('POST', '/me/sendMail')->attachBody($mailBody)->execute();
    }
}
