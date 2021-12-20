<?php

namespace App\Service\GoogleApi;

use App\Entity\Support\Rdv;
use Doctrine\ORM\EntityManagerInterface;
use Google\Exception;
use Google_Client;
use Google_Service_Calendar_Event;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ApiGoogleCalendar
{
    private const SESSION_CLIENT_NAME = 'clientAuthGoogle';
    private const SESSION_CLIENT_TOKEN = 'clientTokenGoogle';

    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    /** @var ContainerInterface */
    private $container;

    /** @var Google_Client */
    private $client;

    /**
     * @throws Exception
     */
    public function __construct(EntityManagerInterface $em, SessionInterface $session, ContainerInterface $container)
    {
        $this->em = $em;
        $this->session = $session;
        $this->container = $container;
        $this->client = $this->getClient();
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function insertGoogleApiToken(String $authCode='')
    {
        if (empty($authCode)) {
            return false;
        }

        $accessToken = $this->getToken($authCode);

        $this->client->fetchAccessTokenWithAuthCode($authCode);
        $this->client->setAccessToken($accessToken);

        if (array_key_exists('error', $accessToken)) {
            return false;
        }

        $service = new \Google_Service_Calendar($this->client);

        $event = $this->getEvent();
        $calendarId = 'primary';
        $optionsParams = [];

        return $service->events->insert($calendarId, $event, $optionsParams)->htmlLink;
    }

    /**
     * Init le client google
     * @throws Exception
     */
    private function getClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setAuthConfig(__DIR__ . '\client_secret.json');
        $client->setApplicationName('app-assia');
        $client->addScope(\Google_Service_Calendar::CALENDAR);
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/rdv/new/response-google-agenda');
        $client->setAccessType('online');
        $client->setIncludeGrantedScopes(true);

        return $client;
    }

    private function getToken($authCode)
    {
//        $this->session->remove(self::SESSION_CLIENT_TOKEN);
//        if (array_key_exists('error', $this->session->get(self::SESSION_CLIENT_TOKEN))) {
//            return $this->session->remove(self::SESSION_CLIENT_TOKEN);
//        }
        if ($this->session->has(self::SESSION_CLIENT_TOKEN)) {
            return $this->session->get(self::SESSION_CLIENT_TOKEN);
        }

        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

        if (!array_key_exists('error', $accessToken)) {
            $this->session->set(self::SESSION_CLIENT_TOKEN, $accessToken);
        }

        return $accessToken;
    }

    public function getOnSessionIsChecked(): bool
    {
        if (!$this->session->has('clientGoogleChecked')) {
            return false;
        }

        return $this->session->get('clientGoogleChecked');
    }

    public function setOnSessionCheckedAndRdvId($rdvId): void
    {
        $this->session->set('clientGoogleChecked', true);
        $this->session->set('clientRdvId', $rdvId);
    }

    private function getEvent(): Google_Service_Calendar_Event
    {
        $rdvId = $this->session->get('clientRdvId');
        /** @var Rdv $rdv */
        $rdv = $this->em->getRepository(Rdv::class)->find($rdvId);
        $timeZone = $rdv->getStart()->getTimezone()->getName();

        return new Google_Service_Calendar_Event([
            'summary' => $rdv->getTitle(),
            'location' => $rdv->getLocation(),
            'description' => $rdv->getContent() .
                '<br><strong>Créé par : </strong>' .
                $this->session->get('_security.last_username') .
                ' (' . $rdv->getCreatedBy()->getEmail() . ')',
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
                'useDefault' => FALSE,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 1),
                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        ]);
    }

}