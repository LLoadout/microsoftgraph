<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\Channel;
use Microsoft\Graph\Model\Chat;
use Microsoft\Graph\Model\ChatMessage;
use Microsoft\Graph\Model\ConversationMember;
use Microsoft\Graph\Model\Team;

/**
 * Teams class for interacting with Microsoft Teams via the Microsoft Graph API
 *
 * This class provides methods to interact with Microsoft Teams, including:
 * - Getting teams and chats
 * - Retrieving chat members and messages
 * - Sending messages to chats and channels
 *
 * @package LLoadout\Microsoftgraph
 */
class Teams
{
    use Connect,
        Authenticate;

    /**
     * Get all teams that the authenticated user has joined
     *
     * @return Team[] Array of Team objects
     */
    public function getJoinedTeams(): array
    {
        return $this->get('/me/joinedTeams', returns: Team::class);
    }

    /**
     * Get all chats that the authenticated user is part of
     *
     * @return Chat[] Array of Chat objects
     */
    public function getChats(): array
    {
        return $this->get('/me/chats', returns: Chat::class);
    }

    /**
     * Get a specific chat by its ID
     *
     * @param string $id The chat ID
     * @return Chat The requested chat object
     */
    public function getChat(string $id): Chat
    {
        return $this->get('/me/chats/'.$id, returns: Chat::class);
    }

    /**
     * Get all members in a specific chat
     *
     * @param Chat $chat The chat object
     * @return ConversationMember[] Array of chat members
     */
    public function getMembersInChat(Chat $chat): array
    {
        return $this->get('/chats/'.$chat->getId().'/members', returns: ConversationMember::class);
    }

    /**
     * Get all channels in a specific team
     *
     * @param Team $team The team object
     * @return Channel[] Array of channels
     */
    public function getChannels(Team $team): array
    {
        return $this->get('/teams/'.$team->getId().'/channels', returns: Channel::class);
    }

    /**
     * Send a message to a chat or channel
     *
     * @param Chat|Channel $chat The chat or channel to send the message to
     * @param string $message The message content (supports HTML)
     * @return GraphResponse The API response
     */
    public function send(Chat|Channel $chat, string $message): GraphResponse
    {
        $data = json_decode('{"body": {"contentType": "html"}}');
        $data->body->content = $message;

        return $this->post('/chats/'.$chat->getId().'/messages', $data);
    }

    /**
     * Get all messages from a specific chat
     *
     * @param Chat $chat The chat object
     * @return ChatMessage[] Array of chat messages
     */
    public function getMessages(Channel|Chat $chat): array
    {
        return $this->get('/chats/'.$chat->getId().'/messages', returns: ChatMessage::class);
    }
}
