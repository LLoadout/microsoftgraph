<?php

namespace LLoadout\Microsoftgraph\Traits;

use Microsoft\Graph\Graph;
use SplFileObject;

trait Mail
{
    public function sendMail($mailable): void
    {
        $graph = (new Graph())->setAccessToken($this->getAccessToken());
        $graph->createRequest('POST', '/me/sendMail')->attachBody($this->getBody($mailable))->execute();
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
