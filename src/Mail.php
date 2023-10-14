<?php

namespace LLoadout\Microsoftgraph;

use Illuminate\Support\Carbon;
use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;

class Mail
{
    use Authenticate, Connect;

    public function sendMail($mailable): void
    {
        $this->post('/me/sendMail', $this->getBody($mailable));
    }

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

    private function getContent($html): array
    {
        return [
            'contentType' => 'html',
            'content' => $html,
        ];
    }

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

    public function getMailFolders()
    {
        $url ='/me/mailfolders';

        return $this->get($url);
    }

    public function getSubFolders($id)
    {
        $url = '/me/mailfolders/' . $id . '/childFolders';

        return $this->get($url);
    }

    public function getMailMessagesFromFolder($folder = 'inbox', $isRead = true, $skip = 0, $limit = 20)
    {
        $url = '/me/mailfolders/' . $folder . '/messages?$select=Id,ReceivedDateTime,Subject,Sender,ToRecipients,From,HasAttachments,InternetMessageHeaders&$skip='.$skip.'&$top='.$limit;
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
            ];
        }

        return $mails;
    }

    public function updateMessage($id, $data)
    {
        $url = '/me/messages/' . $id;

        return $this->patch($url, $data);
    }

    public function moveMessage($id, $destinationId)
    {
        $url = '/me/messages/' . $id . '/move';

        return $this->post($url, ['destinationId' => $destinationId]);
    }

    public function getMessage($id)
    {
        $url = config('socialite.office365.api_url') . '/me/messages/' . $id . '?$select=Id,ReceivedDateTime,createdDateTime,Subject,Sender,ToRecipients,From,HasAttachments,InternetMessageHeaders&$top=10&$skip=0';

        return $this->get($url);
    }

    public function getMessageAttachements($id)
    {
        $url = '/me/messages/' . $id . '/attachments';

        return $this->get($url);
    }
}
