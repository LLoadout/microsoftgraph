<?php

namespace LLoadout\Microsoftgraph\MailManager;

use LLoadout\Microsoftgraph\Mail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

class MicrosoftGraphTransport implements TransportInterface
{
    public function send(RawMessage $message, Envelope $envelope = null): ?SentMessage
    {
        app(Mail::class)->sendMail($message);

        return new SentMessage($message, $envelope);
    }

    public function __toString(): string
    {
        return '';
    }
}
