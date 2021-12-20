<?php

namespace App\Service\GoogleApi;

use App\Entity\Support\Rdv;
use Doctrine\ORM\EntityManagerInterface;
use Google\Exception;
use Google_Client;
use Google_Service_Calendar;
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

    /**
     * Récupère l'url de connexion générée par Google
     * @return string
     */
    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Permet d'enregistrer un nouvel event dans le Google Calendar principal
     * @param String $authCode
     * @return string
     */
    public function insertGoogleApiToken(String $authCode=''): string
    {
        $accessToken = $this->getToken($authCode);
        if (null === $this->client->setAccessToken($accessToken)) {
            $this->session->remove(self::SESSION_CLIENT_TOKEN);
        }

        $this->client->setAccessToken($accessToken);

        $service = new Google_Service_Calendar($this->client);

        $event = $this->getEvent();
        $calendarId = 'primary';
        $optionsParams = [];

        $insert = $service->events->insert($calendarId, $event, $optionsParams);

        $iCalUID = $insert->getICalUID();
        $googleEventId = $insert->getId();

        $this->setEventOnRdv($googleEventId);

        return $insert->htmlLink;
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
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/add-event-google-calendar');
        $client->setAccessType('online');
        $client->setIncludeGrantedScopes(true);

        return $client;
    }

    /**
     * Récupère le token
     * @param string $authCode
     * @return array|mixed
     */
    private function getToken(string $authCode)
    {
//        $this->session->remove(self::SESSION_CLIENT_TOKEN);
//        if (array_key_exists('error', $this->session->get(self::SESSION_CLIENT_TOKEN))) {
//            return $this->session->remove(self::SESSION_CLIENT_TOKEN);
//        }
        if ($this->session->has(self::SESSION_CLIENT_TOKEN)) {
            if (!array_key_exists('error', $this->session->get(self::SESSION_CLIENT_TOKEN))) {
                return $this->session->get(self::SESSION_CLIENT_TOKEN);
            }

            $this->session->remove(self::SESSION_CLIENT_TOKEN);
        }

        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

        if (!array_key_exists('error', $accessToken)) {
            $this->session->remove(self::SESSION_CLIENT_TOKEN);
            $this->session->set(self::SESSION_CLIENT_TOKEN, $accessToken);
        }

        return $accessToken;
    }

    /**
     * Vérifie dans la session si l'option est sélectionnée.
     * @return bool
     */
    public function getOnSessionIsChecked(): bool
    {
        if (!$this->session->has('clientGoogleChecked')) {
            return false;
        }

        return $this->session->get('clientGoogleChecked');
    }

    /**
     * Met la valeur de l'option à true dans la session,
     * car si l'on passe par là, c'est que l'utilisateur a sélectionné l'option.
     * @param $rdvId
     */
    public function setOnSessionCheckedAndRdvId($rdvId): void
    {
        $this->session->set('clientGoogleChecked', true);
        $this->session->set('clientRdvId', $rdvId);
    }

    /**
     * On récupère l'id du rdv enregistré en session pour hydrater un nouvel event.
     * @return Google_Service_Calendar_Event
     */
    private function getEvent(): Google_Service_Calendar_Event
    {
        $rdv = $this->getRdv();
        $timeZone = $rdv->getStart()->getTimezone()->getName();

        return new Google_Service_Calendar_Event([
            'summary' => $rdv->getTitle(),
            'location' => $rdv->getLocation(),
            'description' => $rdv->getContent() .
                '<br><strong>Créé par : </strong>' .
                $this->session->get('_security.last_username') .
                ' (' . $rdv->getCreatedBy()->getEmail() . ')'.
                '<br><strong>Status : </strong>' .
                $rdv->getStatus()
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
                'useDefault' => FALSE,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 1),
                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        ]);
    }

    /**
     * Set l'id de l'event dans le rdv actuel,
     * pour une future update ou delete
     */
    private function setEventOnRdv($setGoogleEventId): void
    {
        $rdv =($this->getRdv())->setGoogleEventId((string)$setGoogleEventId);

        $this->em->persist($rdv);
        $this->em->flush();
    }

    /**
     * Retourne le rdv actuel en fonction de l'id enregistré en session
     * @return Rdv
     */
    private function getRdv(): Rdv
    {
        $rdvId = $this->session->get('clientRdvId');
        return $this->em->getRepository(Rdv::class)->find($rdvId);
    }

}