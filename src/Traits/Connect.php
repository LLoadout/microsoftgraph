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

    private function get($url, $headers = [], $returns = null)
    {
        return $this->call('GET', $url, [], $headers, $returns);
    }

    private function post($url, $data, $headers = [])
    {
        return $this->call('POST', $url, $data, $headers);
    }

    private function patch($url, $data, $headers = [])
    {
        return $this->call('PATCH', $url, $data, $headers);
    }

    private function call($method, $url, $data = [], $headers = [], $returns = null)
    {
        $response = $this->connect()->createRequest($method, $url)->addHeaders($headers)->attachBody($data)->setReturnType($returns)->execute();

        if (blank($returns) && strtolower($method) == 'get') {
            if (isset($response->getBody()['value']))
                return $response->getBody()['value'];
            else
                return $response->getBody();
        }

        return $response;

    }

    public function getMe()
    {
        $url = '/me/';

        return $this->get($url);
    }
}
