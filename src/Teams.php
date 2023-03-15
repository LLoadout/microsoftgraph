<?php

namespace LLoadout\Microsoftgraph;

use Microsoft\Graph\Graph;

class Teams
{
    use \LLoadout\Microsoftgraph\Traits\Authenticate;

    public function send($team, $messsage): array
    {
        $graph = (new Graph())->setAccessToken($this->getAccessToken());
        $data = json_decode('{"body": {"contentType": "html","content": "'.$messsage.'"}}');

        return $graph->createRequest('POST', '/chats/'.$team.'/messages')->attachBody($data)->execute()->getBody();
    }
}
