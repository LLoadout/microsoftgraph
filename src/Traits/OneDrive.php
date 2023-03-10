<?php

namespace LLoadout\Microsoftgraph\Traits;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Microsoft\Graph\Graph;

trait OneDrive
{
    public function listFolders(Mailable $mailable): void
    {
        $graph = new Graph();
        $graph->setAccessToken(session('token'));

        $user = (object) $graph->createRequest('GET', '/me')->execute()->getBody();
        $body = $this->getBody($mailable, $user);

        $graph->createRequest('POST', '/me/sendMail')->attachBody($body)->execute();
    }

    protected function getBody($mailable, $user)
    {
        $envelope = $mailable->envelope();
        $from = new Address($user->mail, $user->displayName);

        return array_filter([
            'message' => [
                'subject' => $envelope->subject,
                'sender' => $this->formatRecipients($from)[0],
                'from' => $this->formatRecipients($from)[0],
                'replyTo' => $this->formatRecipients($envelope->replyTo),
                'toRecipients' => $this->formatRecipients($envelope->to),
                'ccRecipients' => $this->formatRecipients($envelope->cc),
                'bccRecipients' => $this->formatRecipients($envelope->bcc),
                'body' => $this->getContent($mailable),
                'attachments' => $this->toAttachmentCollection($mailable->attachments),
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
            $addresses[] = [
                'emailAddress' => [
                    'name' => $recipients->name,
                    'address' => $recipients->address,
                ],
            ];

            return $addresses;
        }

        foreach ($recipients as $address) {
            $addresses[] = [
                'emailAddress' => [
                    'name' => $address->name,
                    'address' => $address->address,
                ],
            ];
        }

        return $addresses;
    }

    private function getContent(Mailable $mailable): array
    {
        if (! empty($mailable->render())) {
            $content = [
                'contentType' => 'html',
                'content' => $mailable->render(),
            ];
        }

        return $content ?? '';
    }

    protected function toAttachmentCollection($attachments): array
    {
        $collection = [];

        foreach ($attachments as $file) {
            $file = new SplFileObject($file['file'], 'r');
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
}
