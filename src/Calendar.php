<?php

namespace LLoadout\Microsoftgraph;

use Beta\Microsoft\Graph\Model\Event;
use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;
use Microsoft\Graph\Model\DateTimeTimeZone;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Location;
use Microsoft\Graph\Model\PhysicalAddress;

/**
 * Calendar class for interacting with Microsoft Graph Calendar API
 * 
 * This class provides methods to interact with Microsoft Calendar including:
 * - Getting calendars and events
 * - Creating and saving calendar events
 * - Managing event details like attendees, location, and online meeting status
 */
class Calendar
{
    use Connect,
        Authenticate;

    /**
     * Get all calendars for the authenticated user
     * 
     * @return array Array of Calendar objects
     */
    public function getCalendars(): array
    {
        return $this->get('/me/calendars', returns: \Microsoft\Graph\Model\Calendar::class);
    }

    /**
     * Get a single calendar by ID
     * 
     * @param string $calendar_id The ID of the calendar to retrieve
     * @return \Microsoft\Graph\Model\Calendar The requested calendar
     */
    public function getCalendar($calendar_id)
    {
        return $this->get('/me/calendars/'.$calendar_id, returns: \Microsoft\Graph\Model\Calendar::class);
    }

    /**
     * Get all events from a specific calendar
     * 
     * @param \Microsoft\Graph\Model\Calendar $calendar The calendar to get events from
     * @return array Array of Event objects
     */
    public function getCalendarEvents(\Microsoft\Graph\Model\Calendar $calendar): array
    {
        return $this->get('/me/calendars/'.$calendar->getId().'/events', returns: Event::class);
    }

    /**
     * Save an event to a calendar
     * 
     * @param \Microsoft\Graph\Model\Calendar $calendar The calendar to save the event to
     * @param \Microsoft\Graph\Model\Event $event The event to save
     * @return \Microsoft\Graph\Http\GraphResponse|mixed Response from the API
     */
    public function saveEventToCalendar(\Microsoft\Graph\Model\Calendar $calendar, \Microsoft\Graph\Model\Event $event)
    {
        return $this->post('/me/events', $event);
    }

    /**
     * Create a new calendar event with all necessary properties
     * 
     * @param string $starttime Start time of the event in ISO 8601 format
     * @param string $endtime End time of the event in ISO 8601 format
     * @param string $timezone Timezone for the event (e.g. 'Europe/Brussels')
     * @param string $subject Subject/title of the event
     * @param string $body Body/description of the event (HTML supported)
     * @param object $location_address Location object containing address details:
     *                                - address_street: Street name
     *                                - address_house_nr: House number
     *                                - suffix: Address suffix
     *                                - address_zip_code: Postal code
     *                                - address_city: City
     *                                - address_country: Country
     * @param array $attendees Array of email addresses for attendees
     * @param bool $isOnlineMeeting Whether this is an online meeting
     * @return \Microsoft\Graph\Model\Event The created event object
     */
    public function makeEvent(string $starttime, string $endtime, string $timezone, string $subject, string $body, object $location_address, array $attendees = [], bool $isOnlineMeeting = false): \Microsoft\Graph\Model\Event
    {

        $event = app(\Microsoft\Graph\Model\Event::class);
        $event->setSubject($subject);

        $itemBody = new ItemBody();
        $itemBody->setContent($body);
        $itemBody->setContentType('HTML');
        $event->setBody($itemBody);

        $start = new DateTimeTimeZone();
        $start->setDateTime($starttime);
        $start->setTimeZone($timezone);
        $event->setStart($start);

        $end = new DateTimeTimeZone();
        $end->setDateTime($endtime);
        $end->setTimeZone($timezone);
        $event->setEnd($end);

        $event->setIsOnlineMeeting($isOnlineMeeting);

        if ( $location_address ) {
            $address = new PhysicalAddress();
            $address->setStreet($location_address->address_street.' '.$location_address->address_house_nr.trim(' '.$location_address->suffix));
            $address->setPostalCode($location_address->address_zip_code);
            $address->setCity($location_address->address_city);
            $address->setCountryOrRegion($location_address->address_country);

            $location = new Location();
            $location->setAddress($address);
            $event->setLocation($location);
        }

        $arrOfAttendees = [];
        foreach ($attendees as $attendee) {
            $arrOfAttendees[] = (new \Microsoft\Graph\Model\Attendee())->setEmailAddress((new EmailAddress())->setAddress($attendee));
        }
        $event->setAttendees($arrOfAttendees);

        return $event;
    }
}
