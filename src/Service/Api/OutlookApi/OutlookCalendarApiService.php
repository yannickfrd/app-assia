<?php

namespace App\Service\Api\OutlookApi;

use App\Entity\Support\Rdv;
use App\Service\Api\ApiCalendarServiceAbstract;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\BodyType;
use Microsoft\Graph\Model\DateTimeTimeZone;
use Microsoft\Graph\Model\Event;
use Microsoft\Graph\Model\EventType;
use Microsoft\Graph\Model\ItemBody;
use Microsoft\Graph\Model\Location;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class OutlookCalendarApiService extends ApiCalendarServiceAbstract
{
    private const SESSION_CLIENT_OUTLOOK = 'outlookClient';

    private const SESSION_ID_TOKEN_OUTLOOK = 'outlookIdToken';
    private const SESSION_REFRESH_TOKEN_OUTLOOK = 'outlookRefreshToken';
    private const SESSION_ACCESS_TOKEN_OUTLOOK = 'outlookAccessToken';

    private $outlookClientSecret;
    private $outlookClientId;

    /** @var string */
    private $redirectUri;

    /** @var HttpClientInterface */
    private $httpClient;
    public function __construct(
        $outlookClientSecret,
        $outlookClientId,
        UrlGeneratorInterface $generator,
        HttpClientInterface $httpClient
    )
    {
        $this->outlookClientSecret = $outlookClientSecret;
        $this->outlookClientId = $outlookClientId;
        $this->redirectUri = $generator->generate('add_event_outlook_calendar', [],
            UrlGeneratorInterface::ABSOLUTE_URL);
        $this->httpClient = $httpClient;
    }

    /**
     * Get the connexion url, generated by Google Api
     * @return string
     */
    public function getAuthUrl(): string
    {
        return sprintf('https://login.microsoftonline.com/common/oauth2/v2.0/authorize?'.
            'client_id=%s'. '&response_type=%s'. '&redirect_uri=%s'. '&response_mode=%s'. '&scope=%s'
//            . '&prompt=%s'
            ,
            $this->outlookClientId,
            'code',
            $this->redirectUri,
            'query',
            'openid profile email offline_access user.read.all calendars.readwrite'
//            'user.read user.read.all openid profile email calendars.readwrite.shared offline_access'
//            ,'consent'
        );
    }

    /**
     * @param string $authCode
     */
    public function authClient(string $authCode)
    {
        $params = [
            'client_id'=> $this->outlookClientId,
//            'scope'=> 'user.read user.read.all mail.read calendar.read',
//            'user.read user.read.all openid profile email calendars.readwrite offline_access',
            'openid profile email offline_access user.read.all calendars.readwrite',
//            'scope'=> 'mail.read',
            'code'=> $authCode,
            'redirect_uri'=> $this->redirectUri,
            'grant_type'=> 'authorization_code',
            'client_secret'=> $this->outlookClientSecret,
        ];
        $response = $this->httpClient->request('POST',
            'https://login.microsoftonline.com/consumers/oauth2/v2.0/token', [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => $params,
        ]);

        $tokenToArray = $response->toArray();


//        dd($tokenToArray);

        $this->saveToken($response->toArray());

        $client = $this->getClient($tokenToArray);
//        dd($client, $client->toArray());

        return $client;
    }

    /**
     * Save or replace the array token on session
     * @param array $tokenToArray
     */
    private function saveToken(array $tokenToArray): void
    {
        $this->session->set(self::SESSION_ID_TOKEN_OUTLOOK, $tokenToArray['id_token']);
        $this->session->set(self::SESSION_ACCESS_TOKEN_OUTLOOK, $tokenToArray['access_token']);
        $this->session->set(self::SESSION_REFRESH_TOKEN_OUTLOOK, $tokenToArray['refresh_token']);
    }

    /**
     * Save or replace the client Outlook on session
     * @param ResponseInterface $client
     */
    private function saveClient(ResponseInterface $client): void
    {
        $this->session->set(self::SESSION_CLIENT_OUTLOOK, $client->toArray());
    }

    private function getClient(array $tokenToArray)
    {// le token n'est pas pris en session.
//        $this->refreshToken();

        $client = $this->httpClient->request('GET',
            'https://graph.microsoft.com/v1.0/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenToArray['access_token'],
                    'Content-Type' => 'application/json',
                ]
            ]);

//        $this->saveClient($client);

        return $client->toArray();
    }

    private function refreshToken(): void
    {
        $params = [
            'client_id'=> $this->outlookClientId,
            'scope'=> 'openid profile email offline_access user.read.all calendars.readwrite',
            'refresh_token'=> $this->session->get(self::SESSION_REFRESH_TOKEN_OUTLOOK),
            'redirect_uri'=> $this->redirectUri,
            'grant_type'=> 'refresh_token',
            'client_secret'=> $this->outlookClientSecret,
        ];
        $response = $this->httpClient->request('POST',
            'https://login.microsoftonline.com/common/oauth2/v2.0/token', [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded',],
                'body' => $params,
            ]);

        $this->saveToken($response->toArray());
    }

    /**
     *
     * @return string
     * @throws GuzzleException
     * @throws GraphException
     */
    public function addRdv(): string
    {
        $this->refreshToken();

        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));
        $responseEvent = $graph->createRequest('POST', '/me/calendar/events')
            ->attachBody($this->createEvent())
            ->setReturnType(Event::class)
            ->execute()
        ;

        // save event's id in the Rdv selected
        $this->setEventOnRdv('outlook', $responseEvent->getId());

        return $responseEvent->getWebLink();
//        $user = $graph
//            ->createRequest('GET', '/me')
//            ->setReturnType(User::class)
//            ->execute();

//        $calendars = $graph->createRequest('GET', '/me/calendar')
//            ->setReturnType(Calendar::class)
//            ->execute()
//        ;

//        $events = $graph->createRequest('GET', '/me/events')
//            ->setReturnType(Event::class)
//            ->execute()
//        ;
    }

    /**
     * @param int $rdvId
     * @return false|string|null
     * @throws GraphException
     * @throws GuzzleException
     */
    public function update(int $rdvId)
    {
        /** @var Rdv $rdv */
        $rdv = $this->em->getRepository(Rdv::class)->find($rdvId);

        if (null === $rdv->getOutlookEventId()) {
            $this->session->set(self::OUTLOOK_RDV_ID, $rdvId);
            return $this->addRdv();
        }

        if (!$this->eventExist($rdv->getOutlookEventId())) {
            return false;
        }

        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));

        $this->session->set(self::OUTLOOK_RDV_ID, $rdvId);

        $update = $graph->createRequest('PATCh', '/me/events/' . $rdv->getOutlookEventId())
            ->attachBody($this->createEvent())
            ->setReturnType(Event::class)
            ->execute()
        ;

        return $update->getWebLink();
    }

    public function delete(string $eventId)
    {
        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));

        if (!$this->eventExist($eventId)) {
            return false;
        }

        $del = $graph->createRequest('DELETE', '/me/events/' . $eventId)
            ->setReturnType(Event::class)
            ->execute()
        ;

        return isset($del);
    }

    /**
     * Check if the event exists in the Outlook Calendar.
     * @param string $eventId
     * @return bool
     * @throws GraphException
     * @throws GuzzleException
     */
    private function eventExist(string $eventId): bool
    {
        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));

        $event = $graph->createRequest('GET', '/me/events/' . $eventId)
            ->setReturnType(Event::class)
            ->execute()
        ;

        return isset($event);
    }

    /**
     * Create Outlook event
     * @return Event
     */
    private function createEvent(): Event
    {
        /** @var Rdv $rdv */
        $rdv = $this->em->getRepository(Rdv::class)->find($this->session->get(parent::OUTLOOK_RDV_ID));

        $event = (new Event())
            ->setSubject($this->createTitleEvent($rdv))
            ->setStart(new DateTimeTimeZone($this->createDateEvent($rdv->getStart())))
            ->setEnd(new DateTimeTimeZone($this->createDateEvent($rdv->getEnd())))
            ->setType(new EventType('singleInstance'))
            ->setBody(new ItemBody([
                'content' => $this->createBodyEvent(
                    $rdv->getContent(),
                    $rdv->getCreatedBy(),
                    $rdv->getStatus()
                ),
                'contentType' => new BodyType('html')
            ]));

        if ($rdv->getLocation()) { // Set if the Rdv has a location
            $event->setLocation((new Location())->setDisplayName($rdv->getLocation()));
        }
        if ($rdv->getOutlookEventId()) { // set if the Rdv has a EventId
            $event->setId($rdv->getOutlookEventId());
        }

        return $event;
    }

}