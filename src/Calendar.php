<?php

namespace LLoadout\Microsoftgraph;

use Beta\Microsoft\Graph\Model\Event;
use LLoadout\Microsoftgraph\Traits\Authenticate;
use LLoadout\Microsoftgraph\Traits\Connect;
use Microsoft\Graph\Model\DateTimeTimeZone;
use Microsoft\Graph\Model\EmailAddress;
use Microsoft\Graph\Model\ItemBody;

class Calendar
{
    use Connect,
        Authenticate;

    /**
     * Get all contacts
     */
    public function getCalendars(): array
    {
        return $this->get('/me/calendars', returns: \Microsoft\Graph\Model\Calendar::class);
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
     * this is a shortcut to creating an event object and setting all the bases properties.
     */
    public function makeEvent(string $starttime, string $endtime, string $timezone, string $subject, string $body, array $attendees = [], bool $isOnlineMeeting = false): \Microsoft\Graph\Model\Event
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

        $event->setIsOnlineMeeting(true);

        $arrOfAttendees = [];
        foreach ($attendees as $attendee) {
            $arrOfAttendees[] = (new \Microsoft\Graph\Model\Attendee())->setEmailAddress((new EmailAddress())->setAddress($attendee));
        }
        $event->setAttendees($arrOfAttendees);

        return $event;
    }
}
