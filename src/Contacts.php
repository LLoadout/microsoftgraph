<?php

namespace LLoadout\Microsoftgraph;

use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;
use Microsoft\Graph\Model\Contact;

/**
 * Class Contacts
 *
 * This class provides methods to interact with Microsoft Graph API's Contacts endpoints.
 * It allows managing contacts in a Microsoft account including creating, reading,
 * updating and deleting contact records.
 *
 * @package LLoadout\Microsoftgraph
 */
class Contacts
{
    private $model = Contact::class;

    use Connect,
        Authenticate;

    /**
     * Get all contacts from the authenticated user's contact list
     *
     * @return Contact[] Array of Contact objects
     */
    public function getContacts(): array
    {
        return self::get('/me/contacts', returns: Contact::class);
    }

    /**
     * Create a new contact in the authenticated user's contact list
     *
     * @param Contact $contact The contact object containing the contact details
     * @return Contact The created contact object with server-generated fields
     */
    public function storeContact(Contact $contact): Contact
    {
        return self::post('/me/contacts', json_encode($contact), returns: Contact::class);
    }

    /**
     * Retrieve a specific contact by ID
     *
     * @param string $id The unique identifier of the contact
     * @return Contact The requested contact object
     */
    public function getContact(string $id): Contact
    {
        return self::get('/me/contacts/'.$id, returns: Contact::class);
    }

    /**
     * Update an existing contact's information
     *
     * @param string $id The unique identifier of the contact to update
     * @param Contact $contact The contact object containing the updated information
     * @return Contact The updated contact object
     */
    public function updateContact(string $id, Contact $contact): Contact
    {
        return self::patch('/me/contacts/'.$id, json_encode($contact), returns: Contact::class);
    }

    /**
     * Delete a contact from the authenticated user's contact list
     *
     * @param string $id The unique identifier of the contact to delete
     * @return mixed The response from the delete operation
     */
    public function deleteContact(string $id): mixed
    {
        return self::delete('/me/contacts/'.$id);
    }

}
