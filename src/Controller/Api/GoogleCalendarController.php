<?php

namespace App\Controller\Api;

use App\Service\GoogleApi\ApiGoogleCalendar;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GoogleCalendarController extends AbstractController
{
    /** @var ApiGoogleCalendar */
    private $gapi;

    public function __construct(ApiGoogleCalendar $gapi)
    {
        $this->gapi = $gapi;
    }

    /**
     * @Route("/auth-google-calendar", name="auth_google_calendar", methods={"GET"})
     */
    public function authClientGoogleCalendar(Request $request): JsonResponse
    {
        $this->gapi->setOnSessionCheckedAndRdvId($request->query->get('rdv_id'));

        return $this->json($this->gapi->getAuthUrl());
    }

    /**
     * @Route("/add-event-google-calendar", name="add_event_google_calendar")
     */
    public function getResponseGoogleAgenda(Request $request): RedirectResponse
    {
        $authCode = $request->query->get('code');

        if (!empty($authCode)) {
            $urlResponse = $this->gapi->insertGoogleApiToken($authCode);

            return $this->redirect($urlResponse);
        }

        return $this->redirect($this->gapi->getAuthUrl());
    }
}
