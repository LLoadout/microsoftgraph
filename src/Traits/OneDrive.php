<?php

namespace LLoadout\Microsoftgraph\Traits;

use Illuminate\Mail\Mailable;
use Microsoft\Graph\Graph;

trait OneDrive
{
    public function listFolders(Mailable $mailable): void
    {
        $graph = (new Graph())->setAccessToken($this->getAccessToken());
    }
}
