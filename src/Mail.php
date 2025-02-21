<?php

namespace LLoadout\Microsoftgraph;

use Illuminate\Support\Carbon;
use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;

/**
 * Mail class for interacting with Microsoft Graph API's mail functionality
 *
 * This class provides methods to interact with Microsoft Graph API's mail features including:
 * - Sending emails with attachments
 * - Managing mail folders
 * - Reading and managing messages
 * - Moving messages between folders
 * - Retrieving message attachments
 *
 * @package LLoadout\Microsoftgraph
 */
class Mail
{
    use Authenticate, Connect;

    /**
     * Send an email using Microsoft Graph API
     *
     * @param mixed $mailable The mailable object containing email details
     * @return void
     */
    public function sendMail($mailable): void
    {
        $this->post('/me/sendMail', $this->getBody($mailable));
    }

    /**
     * Prepare the email body for sending
     *
     * @param mixed $mailable The mailable object
     * @return array The formatted email body
     */
    protected function getBody($mailable)
    {
        $html = $mailable->getHtmlBody();
        $from = $mailable->getFrom();
        $to = $mailable->getTo();
        $cc = $mailable->getCc();
        $bcc = $mailable->getBcc();
        $replyTo = $mailable->getReplyTo();
        $subject = $mailable->getSubject();

        return array_filter([
            'message' => [
                'subject' => $subject,
                'sender' => $this->formatRecipients($from)[0],
                'from' => $this->formatRecipients($from)[0],
                'replyTo' => $this->formatRecipients($replyTo),
                'toRecipients' => $this->formatRecipients($to),
                'ccRecipients' => $this->formatRecipients($cc),
                'bccRecipients' => $this->formatRecipients($bcc),
                'body' => $this->getContent($html),
                'attachments' => $this->toAttachmentCollection($mailable->getAttachments()),
            ],
        ]);
    }

    /**
     * Format email recipients into Microsoft Graph API format
     *
     * @param mixed $recipients Single recipient or array of recipients
     * @return array Formatted recipients array
     */
    protected function formatRecipients($recipients): array
    {
        $addresses = [];

        if (! $recipients) {
            return $addresses;
        }

        if (! is_countable($recipients)) {
            $recipients = [$recipients];
        }

        foreach ($recipients as $address) {
            $addresses[] = [
                'emailAddress' => [
                    'name' => $address->getName(),
                    'address' => $address->getAddress(),
                ],
            ];
        }

        return $addresses;
    }

    /**
     * Format email content into Microsoft Graph API format
     *
     * @param string $html HTML content of the email
     * @return array Formatted content array
     */
    private function getContent($html): array
    {
        return [
            'contentType' => 'html',
            'content' => $html,
        ];
    }

    /**
     * Convert attachments into Microsoft Graph API format
     *
     * @param array $attachments Array of attachment files
     * @return array Formatted attachments array
     */
    protected function toAttachmentCollection($attachments): array
    {
        $collection = [];

        foreach ($attachments as $file) {
            $file = new \SplFileObject($file['file'], 'r');
            $body = $file->fread($file->getSize());
            $imgdata = base64_decode($body);
            $f = finfo_open();
            $contentType = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);

            $collection[] = [
                'name' => $file->getFilename(),
                'contentId' => $file->getInode().'@lloadout.graph',
                'contentBytes' => base64_encode($body),
                'contentType' => $contentType,
                'size' => strlen($body),
                '@odata.type' => '#microsoft.graph.fileAttachment',
                'isInline' => true,
            ];
        }

        return $collection;
    }

    /**
     * Get all mail folders for the authenticated user
     *
     * @return array Mail folders
     */
    public function getMailFolders()
    {
        $url ='/me/mailfolders';

        return $this->get($url);
    }

    /**
     * Get subfolders for a specific mail folder
     *
     * @param string $id Parent folder ID
     * @return array Subfolders
     */
    public function getSubFolders($id)
    {
        $url = '/me/mailfolders/' . $id . '/childFolders';

        return $this->get($url);
    }

    /**
     * Get messages from a specific mail folder
     *
     * @param string $folder Folder name (default: 'inbox')
     * @param bool $isRead Include read messages (default: true)
     * @param int $skip Number of messages to skip (default: 0)
     * @param int $limit Maximum number of messages to return (default: 20)
     * @return array Messages
     */
    public function getMailMessagesFromFolder($folder = 'inbox', $isRead = true, $skip = 0, $limit = 20)
    {
        $url = '/me/mailfolders/' . $folder . '/messages?$select=Id,ReceivedDateTime,Subject,Sender,ToRecipients,From,Body,HasAttachments,InternetMessageHeaders&$skip='.$skip.'&$top='.$limit;
        if (! $isRead) {
            $url .= '&$filter=isRead ne true';
        }

        $response = $this->get($url);

        $mails    = [];
        foreach ($response as $mail) {
            $to = optional(collect($mail['internetMessageHeaders'])->keyBy('name')->get('X-Rcpt-To'))['value'];

            $mails[] = [
                'id'           => $mail['id'],
                'date'         => Carbon::parse($mail['receivedDateTime'])->format('d-m-Y H:i'),
                'subject'      => $mail['subject'],
                'from'         => $mail['from']['emailAddress'],
                'to'           => ! blank($to) ? $to : optional($mail['toRecipients'])[0]['emailAddress']['address'],
                'attachements' => $mail['hasAttachments'],
                'body'         => $mail['body']['content'],
            ];
        }

        return $mails;
    }

    /**
     * Update a message
     *
     * @param string $id Message ID
     * @param array $data Update data
     * @return mixed API response
     */
    public function updateMessage($id, $data)
    {
        $url = '/me/messages/' . $id;

        return $this->patch($url, $data);
    }

    /**
     * Move a message to a different folder
     *
     * @param string $id Message ID
     * @param string $destinationId Destination folder ID
     * @return mixed API response
     */
    public function moveMessage($id, $destinationId)
    {
        $url = '/me/messages/' . $id . '/move';

        return $this->post($url, ['destinationId' => $destinationId]);
    }

    /**
     * Get a specific message by ID
     *
     * @param string $id Message ID
     * @return mixed Message details
     */
    public function getMessage($id)
    {
        $url = config('socialite.office365.api_url') . '/me/messages/' . $id . '?$select=Id,ReceivedDateTime,createdDateTime,Subject,Sender,ToRecipients,From,HasAttachments,InternetMessageHeaders&$top=10&$skip=0';

        return $this->get($url);
    }

    /**
     * Get attachments for a specific message
     *
     * @param string $id Message ID
     * @return mixed Message attachments
     */
    public function getMessageAttachements($id)
    {
        $url = '/me/messages/' . $id . '/attachments';

        return $this->get($url);
    }
}
