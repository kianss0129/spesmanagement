<?php

namespace App\Services;

use Google\Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        $client = new Client();

        // Load credentials (service account JSON or OAuth JSON path)
        $client->setAuthConfig(config('google.credentials_path'));

        // Calendar scope
        $client->addScope(Google_Service_Calendar::CALENDAR);

        // Offline access
        $client->setAccessType('offline');

        // Prevent prompt loop
        $client->setPrompt('select_account consent');

        $this->client = $client;
    }

    /**
     * Create a Google Calendar event with Google Meet conferencing data.
     *
     * @param string $summary
     * @param string $startDateTime ISO8601 string e.g. 2025-12-10T14:00:00
     * @param string $endDateTime   ISO8601 string
     * @param array  $attendees     array of emails
     * @return Google_Service_Calendar_Event
     * @throws \Google_Service_Exception|\Google_Exception
     */
    public function createInterviewEvent(string $summary, string $startDateTime, string $endDateTime, array $attendees = [])
    {
        $service = new Google_Service_Calendar($this->client);

        $event = new Google_Service_Calendar_Event([
            'summary' => $summary,
            'start' => [
                'dateTime' => $startDateTime,
                'timeZone' => 'Asia/Manila',
            ],
            'end' => [
                'dateTime' => $endDateTime,
                'timeZone' => 'Asia/Manila',
            ],
            'attendees' => array_map(fn($email) => ['email' => $email], $attendees),
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => uniqid('meet_'),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ],
        ]);

        $created = $service->events->insert(
            config('google.calendar_id', 'primary'),
            $event,
            ['conferenceDataVersion' => 1]
        );

        return $created;
    }
}
