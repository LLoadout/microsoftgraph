<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\Channel;
use Microsoft\Graph\Model\Chat;
use Microsoft\Graph\Model\ConversationMember;
use Microsoft\Graph\Model\Team;

class Teams
{
    use Connect,
        Authenticate;

    /**
     * Get all teams that you have joined
     */
    public function getJoinedTeams(): array
    {
        return $this->get('/me/joinedTeams', returns: Team::class);
    }

    /**
     * Get all the chats that you have
     */
    public function getChats(): array
    {
        return $this->get('/me/chats', returns: Chat::class);
    }

    /**
     * Get a specific chat by id
     */
    public function getChat(string $id): Chat
    {
        return $this->get('/me/chats/'.$id, returns: Chat::class);
    }

    /**
     * Get all the members in a chat
     */
    public function getMembersInChat(Chat $chat): array
    {
        return $this->get('/chats/'.$chat->getId().'/members', returns: ConversationMember::class);
    }

    /**
     * Get all the channels in a team
     */
    public function getChannels(Team $team): array
    {
        return $this->get('/teams/'.$team->getId().'/channels', returns: Channel::class);
    }

    /**
     * Send a message to a chat
     */
    public function send(Chat|Channel $chat, string $message): GraphResponse
    {

        $data = json_decode('{"body": {"contentType": "html"}}');
        $data->body->content = $message;

        return $this->post('/chats/'.$chat->getId().'/messages', $data);
    }
}
