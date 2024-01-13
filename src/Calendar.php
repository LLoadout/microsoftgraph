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

class Calendar
{
    use Connect,
        Authenticate;

    /**
     * Get all calendars
     */
    public function getCalendars(): array
    {
        return $this->get('/me/calendars', returns: \Microsoft\Graph\Model\Calendar::class);
    }

    /**
     * Get single calendars
     */
    public function getCalendar($calendar_id)
    {
        return $this->get('/me/calendars/'.$calendar_id, returns: \Microsoft\Graph\Model\Calendar::class);
    }

    /**
     * Get all events in a calendar
     */
    public function getCalendarEvents(\Microsoft\Graph\Model\Calendar $calendar): array
    {
        return $this->get('/me/calendars/'.$calendar->getId().'/events', returns: Event::class);
    }

    /**
     * Save an event to a calendar
     *
     * @return \Microsoft\Graph\Http\GraphResponse|mixed
     */
    public function saveEventToCalendar(\Microsoft\Graph\Model\Calendar $calendar, \Microsoft\Graph\Model\Event $event)
    {
        return $this->post('/me/events', $event);
    }

    /**
     * Make an event and return an event object of the type \Microsoft\Graph\Model\Event
     * this is a shortcut for creating an event object and setting all the bases properties.
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
