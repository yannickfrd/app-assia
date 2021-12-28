<?php

namespace App\Service\Api\GoogleApi;

use App\Entity\Support\Rdv;
use App\Service\Api\ApiCalendarServiceAbstract;
use Doctrine\ORM\EntityManagerInterface;
use Google\Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GoogleCalendarApiService extends ApiCalendarServiceAbstract
{
    private const SESSION_CLIENT_ID = 'clientRdvId';
    private const SESSION_CLIENT_TOKEN = 'clientTokenGoogle';
    private const SESSION_CLIENT_REFRESH_TOKEN = 'clientRefreshTokenGoogle';

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em) {
//        parent::__construct($session);
        $this->em = $em;
    }

    /**
     * Get the connexion url, generated by Google Api
     * @return string
     * @throws Exception
     */
    public function getAuthUrl(): string
    {
        $client = $this->getClientDefault();
        $client->setPrompt('select_account');

        return $client->createAuthUrl();
    }

    /**
     * Create new event on primary calendar
     * @param String $authCode
     * @return string
     * @throws Exception
     */
    public function insertGoogleApiToken(String $authCode): string
    {
        $service = $this->createServiceCalendar();

        $calendarId = 'primary';
        $optionsParams = [];
        $event = $service->events->insert($calendarId, $this->createEvent(), $optionsParams);

        $this->setEventOnRdv($event->getId());

        return $event->htmlLink;
    }

    /**
     * Update an event on primary calendar
     * @param int $rdvId
     * @return bool
     * @throws Exception
     */
    public function update(int $rdvId): bool
    {
        /** @var Rdv $rdv */
        $rdv = $this->em->getRepository(Rdv::class)->find($rdvId);

        $service = $this->createServiceCalendar();

        $getEvent = $service->events->get('primary', $rdv->getGoogleEventId());
        if (!$getEvent->count()) {
            return false;
        }

        // Hydration
        $event = $this->createEvent($rdv);
        $event->setId($getEvent->getId());

        $updateResponse = $service->events->update('primary', $getEvent->getId(), $event);

        return ($updateResponse->getStatus() === 'confirmed');
    }

    /**
     * Delete the event on primary calendar
     * @param string $googleEventId
     * @throws Exception
     */
    public function deleteEvent(string $googleEventId): void
    {
        $service = $this->createServiceCalendar();
        $service->events->delete('primary', $googleEventId);
    }


    /**
     * Create a new Calendar Service
     * @return Google_Service_Calendar
     * @throws Exception
     */
    private function createServiceCalendar(): Google_Service_Calendar
    {
        return new Google_Service_Calendar($this->getClient());
    }

    /**
     * Make a new Google Client by default
     * @return Google_Client
     * @throws Exception
     */
    private function getClientDefault(): Google_Client
    {
        $client = new Google_Client();
        $client->setAuthConfig(__DIR__ . '\client_secret.json');
        $client->setApplicationName('app-assia');
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/add-event-google-calendar');
        $client->setAccessType('offline');
        $client->setApprovalPrompt("none");
        $client->setIncludeGrantedScopes(true);

        return $client;
    }

    /**
     * Init Google Client
     * @param string|null $authCode
     * @return Google_Client
     * @throws Exception
     */
    private function getClient(string $authCode = null): Google_Client
    {
        $client = $this->getClientDefault();

        if (!$authCode && $this->session->has(self::SESSION_CLIENT_TOKEN)) {
            $accessToken = $this->session->get(self::SESSION_CLIENT_TOKEN);
            $client->setAccessToken($accessToken);

            if (!$client->isAccessTokenExpired()) {
                $client->refreshToken($this->session->get(self::SESSION_CLIENT_REFRESH_TOKEN));
                $this->session->set(self::SESSION_CLIENT_TOKEN, $client->getAccessToken());
            }

            return $client;
        }

        $accessToken = $this->getToken($client, $authCode);
        $client->setAccessToken($accessToken);

        // Update token
        $this->session->set(self::SESSION_CLIENT_TOKEN, $client->getAccessToken());
        // Save refresh_token. If the client have a "refresh_token", we save or change it on session
        if (null !== $client->getRefreshToken()) {
            $this->session->set(self::SESSION_CLIENT_REFRESH_TOKEN, $client->getRefreshToken());
        }

        return $client;
    }

    /**
     * @param Google_Client $client
     * @param string|null $authCode
     * @return array
     */
    private function getToken(Google_Client $client, string $authCode=null): array
    {
        if ($authCode) {
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            if (!array_key_exists('error', $accessToken)) {
                $this->session->set(self::SESSION_CLIENT_TOKEN, $accessToken);
            }

            if ($client->isAccessTokenExpired()) {
                $client->refreshToken($this->session->get(self::SESSION_CLIENT_REFRESH_TOKEN));
                $this->session->set(self::SESSION_CLIENT_TOKEN, $client->getAccessToken());
            }

            return $accessToken;
        }

        if (
            $this->session->has(self::SESSION_CLIENT_TOKEN) &&
            array_key_exists('error', $this->session->get(self::SESSION_CLIENT_TOKEN))
        ) {
            $client->refreshToken($this->session->get(self::SESSION_CLIENT_REFRESH_TOKEN));
            $this->session->set(self::SESSION_CLIENT_TOKEN, $client->getAccessToken());

            return $client->getAccessToken();
        }

        return $this->session->get(self::SESSION_CLIENT_TOKEN);
    }

    /**
     * Set the value of the option to true in the session,
     * because if it is passed through, it means that the user has selected the option.
     * @param $rdvId
     */
    public function setOnSessionCheckedAndRdvId($rdvId): void
    {
        $this->session->set(parent::CLIENT_GOOGLE_CHECKED, true);
        $this->session->set(self::SESSION_CLIENT_ID, $rdvId);
    }

    /**
     * Get the id of the rdv recorded in session or in parameter to hydrate a new event.
     * @param Rdv|null $rdv
     * @return Google_Service_Calendar_Event
     */
    private function createEvent(Rdv $rdv=null): Google_Service_Calendar_Event
    {
        if (!$rdv) {
            $rdv = $this->getRdv();
        }

        $summary = $rdv->getTitle();
        if ($rdv->getSupportGroup()) {
            $pattern = '/' . $rdv->getSupportGroup()->getHeader()->getFullname() . '/';
            preg_match($pattern, $rdv->getTitle(), $matches);

            if (empty($matches)) {
                $summary .= ' | ' . $rdv->getSupportGroup()->getHeader()->getFullname();
            }
        }

        $timeZone = $rdv->getStart()->getTimezone()->getName();
        $status = $rdv->getStatus() ? '<br><strong>Statut : </strong>' . $rdv->getStatus() : '';

        return new Google_Service_Calendar_Event([
            'summary' => $summary,
            'location' => $rdv->getLocation(),
            'description' => $rdv->getContent() .
                '<br><strong>Créé par : </strong>' . $rdv->getCreatedBy()->getFullname() .
                $status
            ,
            'start' => array(
                'dateTime' => $rdv->getStart()->format('c'),
                'timeZone' => $timeZone,
            ),
            'end' => array(
                'dateTime' => $rdv->getEnd()->format('c'),
                'timeZone' => $timeZone,
            ),
            'recurrence' => array(
                'RRULE:FREQ=DAILY;COUNT=1'
            ),
            'attendees' => array( // Rattacher des utilisateurs
//                array('email' => 'sbrin@example1.com'),
//                array('email' => 'sbrin@example2.com'),
            ),
            'reminders' => array(
                'useDefault' => TRUE,
                'overrides' => array(
//                    array('method' => 'email', 'minutes' => 1),
//                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        ]);
    }

    /**
     * Update th Rdv and set the event Id Google Calendar
     * @param string $GoogleEventId
     */
    private function setEventOnRdv(string $GoogleEventId): void
    {
        $rdv = ($this->getRdv())->setGoogleEventId($GoogleEventId);

        $this->em->persist($rdv);
        $this->em->flush();
    }

    /**
     * Returns the current Rdv recorded in session according to the id
     * @return Rdv
     */
    private function getRdv(): Rdv
    {
        return $this->em->getRepository(Rdv::class)->find($this->session->get(self::SESSION_CLIENT_ID));
    }

}