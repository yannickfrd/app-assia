<?php

namespace App\Service\GoogleApi;

use Google\Service\Calendar;
use Google_Client;
use Google_Exception;
use Google_Service_Calendar;
use Symfony\Component\HttpFoundation\InputBag;

class GoogleAgenda
{
    private $API_KEY = "AIzaSyBbf4Nc3NGPJ-mHysK2DkbDx-FFn2y5Xz0";
    private $OAUTH_CLI_ID = "226888224425-jl18mogr4dpouu479j1vbjq3558e9r6k.apps.googleusercontent.com";
    private $OAUTH_CLI_SECRET = "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";

    protected $client = null;

    /**
     * @throws Google_Exception
     */
    public function __construct()
    {
        $this->client = $this->getClient();
    }

    /**
     * @return string
     */
    public function sync(): string
    {
        // 1.L'app identifie les autorisations dont elle a besoin.
//        $client = $this->getClient();
        // 2. Votre application redirige l'utilisateur vers Google avec la liste des autorisations demandées.
        $authUrl = $this->client->createAuthUrl();
        return $authUrl;
        // 3. L'utilisateur décide d'accorder ou non les autorisations à votre application.
        // 4. Votre application découvre ce que l'utilisateur a décidé.
        // 5. Si l'utilisateur a accordé les autorisations demandées,
            // votre application récupère les jetons nécessaires pour effectuer des demandes d'API au nom de l'utilisateur.
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     * @throws Google_Exception
     */
    private function getClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setAuthConfig(__DIR__ . '\client_secret.json');
        $client->setApplicationName('app-assia');
        $client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        $client->setRedirectUri('https://' . $_SERVER['HTTP_HOST'] . '/rdv/new/response-google-agenda');
        $client->setAccessType('online');        // online access
        $client->setIncludeGrantedScopes(true);   // incremental auth

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
//        $tokenPath = 'token.json';
//        if (file_exists($tokenPath)) {
//            $accessToken = json_decode(file_get_contents($tokenPath), true);
//            $client->setAccessToken($accessToken);
//        }
//
//        // If there is no previous token or it's expired.
//        if ($client->isAccessTokenExpired()) {
//            // Refresh the token if possible, else fetch a new one.
//            if ($client->getRefreshToken()) {
//                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//            } else {
//                // Request authorization from the user.
//                $authUrl = $client->createAuthUrl();
//                $authCode = trim(fgets(fopen("php://stdin", 'rb')));
//
//                // Exchange authorization code for an access token.
//                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
//                $client->setAccessToken($accessToken);
//
//                // Check to see if there was an error.
//                if (array_key_exists('error', $accessToken)) {
//                    throw new Exception(join(', ', $accessToken));
//                }
//            }
//            // Save the token to a file.
//            if (!file_exists(dirname($tokenPath))) {
//                mkdir(dirname($tokenPath), 0700, true);
//            }
//            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
//        }

        return $client;
    }

    public function getQuery(string $code)
    {
        // Pour échanger un code d'autorisation contre un token
        $access_token = $this->getToken('code');

//        $calendar = new Google_Service_Calendar($this->client);
        $calendar = new Calendar($this->client);

        $calendarId = 'primary';
        $optionsParams = [
            'maxResults' => 1,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        ];

//        $a = $calendar->events->quickAdd($calendarId, );
//        $a = $calendar->;
        $results = $calendar->events->listEvents($calendarId, $optionsParams);
        $events = $results->getItems();
//        dd($calendar, $results, $events);

        dd($this->client);
    }

    public function getToken(string $code): void
    {
        $this->client->authenticate($code);
        $access_token = $this->client->getAccessToken();
        $this->client->setAccessToken($access_token);
    }

}

//// If there is no previous token or it's expired.
//if ($client->isAccessTokenExpired()) {
//    // Refresh the token if possible, else fetch a new one.
//    if ($client->getRefreshToken()) {
//        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//    } else {
//        // Request authorization from the user.
//        $authUrl = $client->createAuthUrl();
//        printf("Open the following link in your browser:\n%s\n", $authUrl);
//        print 'Enter verification code: ';
//        $authCode = trim(fgets(STDIN));
//
//        // Exchange authorization code for an access token.
//        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
//        $client->setAccessToken($accessToken);
//
//        // Check to see if there was an error.
//        if (array_key_exists('error', $accessToken)) {
//            throw new Exception(join(', ', $accessToken));
//        }
//    }
//    // Save the token to a file.
//    if (!file_exists(dirname($tokenPath))) {
//    mkdir(dirname($tokenPath), 0700, true);
//    }
//    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
//}
//return $client;
//}
//
//
//// Get the API client and construct the service object.
//$client = getClient();
//$service = new Google_Service_Calendar($client);
//
//// Print the next 10 events on the user's calendar.
//$calendarId = 'primary';
//$optParams = array(
//'maxResults' => 10,
//'orderBy' => 'startTime',
//'singleEvents' => true,
//'timeMin' => date('c'),
//);
//$results = $service->events->listEvents($calendarId, $optParams);
//$events = $results->getItems();
//
//if (empty($events)) {
//    print "No upcoming events found.\n";
//} else {
//    print "Upcoming events:\n";
//    foreach ($events as $event) {
//        $start = $event->start->dateTime;
//        if (empty($start)) {
//            $start = $event->start->date;
//        }
//        printf("%s (%s)\n", $event->getSummary(), $start);
//    }
//}