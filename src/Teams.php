<?php

namespace LLoadout\Microsoftgraph;

use Microsoft\Graph\Graph;

class Teams
{
    use \LLoadout\Microsoftgraph\Traits\Authenticate;

    public function graph(): Graph
    {
        return (new Graph())->setAccessToken($this->getAccessToken());
    }
    public function getTeams(): array
    {
        return $this->graph()->createRequest('GET', '/me/joinedTeams')->execute()->getBody()['value'];
    }

    public function getChats(): array
    {
        return $this->graph()->createRequest('GET', '/me/chats')->execute()->getBody()['value'];
    }

    public function getMembersInChat($chat): array
    {
        return $this->graph()->createRequest('GET', '/chats/'.$chat['id'].'/members')->execute()->getBody()['value'];
    }

    public function getContacts(): array
    {
        return $this->graph()->createRequest('GET', '/me/contacts')->execute()->getBody();
    }

    public function getChannels($team): array
    {
        return $this->graph()->createRequest('GET', '/teams/'.$team.'/channels')->execute()->getBody();
    }
    public function send($chat, $messsage): array
    {
        $data = json_decode('{"body": {"contentType": "html","content": "'.$messsage.'"}}');

        return $this->graph()->createRequest('POST', '/chats/'.$chat['id'].'/messages')->attachBody($data)->execute()->getBody();
    }
}
