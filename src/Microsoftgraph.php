<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Mail;

class Microsoftgraph
{
    use Mail, Authenticate;


    public function getAccessToken()
    {
        return session('token');
    }
}
