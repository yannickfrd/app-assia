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
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OutlookCalendarApiService extends ApiCalendarServiceAbstract
{
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
//            ,'consent'
        );
    }

    /**
     * Authentication
     * @param string $authCode
     * @return array
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function authClient(string $authCode): array
    {
        $params = [
            'client_id'=> $this->outlookClientId,
            'openid profile email offline_access user.read.all calendars.readwrite',
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
        $this->saveToken($response->toArray());

        return $this->getClient($tokenToArray);
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

    private function getClient(array $tokenToArray): array
    {
        $client = $this->httpClient->request('GET',
            'https://graph.microsoft.com/v1.0/me', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $tokenToArray['access_token'],
                    'Content-Type' => 'application/json',
                ]
            ]);

        return $client->toArray();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
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
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Connection' => 'keep-alive'
                    ],
                'body' => $params,
            ]);

        $this->saveToken($response->toArray());
    }

    /**
     * Creating an event in the Outlook calendar.
     * @return string
     * @throws GuzzleException
     * @throws GraphException
     */
    public function addRdv(): string
    {
//        $this->refreshToken();

        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));
        $responseEvent = $graph->createRequest('POST', '/me/calendar/events')
            ->attachBody($this->createEvent())
            ->setReturnType(Event::class)
            ->execute();

        // save event's id in the Rdv selected
        $this->setEventOnRdv('outlook', $responseEvent->getId());

        return $responseEvent->getWebLink();
    }

    /**
     * @param int $rdvId
     * @return false|string|null
     * @throws GraphException
     * @throws GuzzleException
     */
    public function update(int $rdvId)
    {
//        $this->refreshToken();

        /** @var Rdv $rdv */
        $rdv = $this->em->getRepository(Rdv::class)->find($rdvId);

        $this->session->set('outlookRdvId', $rdvId);

        if (null === $rdv->getOutlookEventId() || !$this->eventExists($rdv->getOutlookEventId())) {
            return $this->addRdv();
        }

        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));
        $update = $graph->createRequest('PATCh', '/me/events/' . $rdv->getOutlookEventId())
            ->attachBody($this->createEvent())
            ->setReturnType(Event::class)
            ->execute();

        return isset($update);
    }

    /**
     * Deleting an event in the Outlook calendar.
     * @param string $eventId
     * @return bool
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws GraphException
     * @throws GuzzleException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function delete(string $eventId): bool
    {
        $this->refreshToken();

        if (!$this->eventExists($eventId)) {
            return false;
        }

        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));
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
     * @throws GuzzleException
     */
    private function eventExists(string $eventId): bool
    {
        $graph = (new Graph())->setAccessToken($this->session->get(self::SESSION_ACCESS_TOKEN_OUTLOOK));

        try {
            $event = $graph->createRequest('GET', '/me/events/' . $eventId)
                ->setReturnType(Event::class)
                ->execute();
        } catch (\Exception $e) {
            if (404 === $e->getCode()) {
                return false;
            }
        }

        return isset($event);
    }



    /**
     * Create Outlook event.
     * @return Event
     */
    private function createEvent(): Event
    {
        $rdv = $this->getRdv('outlook');

        $event = (new Event())
            ->setSubject($this->createTitleEvent($rdv))
            ->setStart(new DateTimeTimeZone($this->createDateEvent($rdv->getStart())))
            ->setEnd(new DateTimeTimeZone($this->createDateEvent($rdv->getEnd())))
            ->setType(new EventType('singleInstance'))
            ->setBody(new ItemBody([
                'content' => $this->createBodyEvent(
                    $rdv->getContent(),
                    $rdv->getCreatedBy(),
                    $rdv->getStatusToString()
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