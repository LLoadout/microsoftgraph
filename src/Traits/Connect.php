<?php

namespace LLoadout\Microsoftgraph\Traits;

use Microsoft\Graph\Graph;

trait Connect
{
    private $connection;

    private function connect(): Graph
    {
        if (blank($this->connection)) {
            $this->connection = (new Graph())->setAccessToken($this->getAccessToken());
        }

        return $this->connection;
    }

    private function get($url)
    {
        return $this->connect()->createRequest('GET', $url)->execute()->getBody()['value'];
    }

    private function post($url, $data)
    {
        return $this->connect()->createRequest('POST', $url)->attachBody($data)->execute()->getBody();
    }
}
