<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;

class Teams
{
    use Connect,
        Authenticate;

    /**
     * Get all teams that you have joined
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getJoinedTeams(): array
    {
        return $this->get('/me/joinedTeams');
    }

    /**
     * Get all the chats that you have
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getChats(): array
    {
        return $this->get('/me/chats');
    }

    /**
     * Get all the members in a chat
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getMembersInChat($chat): array
    {
        return $this->get('/chats/'.$chat['id'].'/members');
    }

    /**
     * Get all the channels in a team
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function getChannels($team): array
    {
        return $this->get('/teams/'.$team['id'].'/channels');
    }

    /**
     * Send a message to a chat
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    public function send($chat, $message): array
    {

        $data = json_decode('{"body": {"contentType": "html"}}');
        $data->body->content = $message;

        return $this->post('/chats/'.$chat['id'].'/messages', $data);
    }
}
