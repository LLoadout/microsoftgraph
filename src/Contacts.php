<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;
use Microsoft\Graph\Model\Contact;

class Contacts
{
    private $model = Contact::class;

    use Connect,
        Authenticate;

    /**
     * Get all contacts
     */
    public function getContacts(): array
    {
        return self::get('/me/contacts', returns: Contact::class);
    }
}
