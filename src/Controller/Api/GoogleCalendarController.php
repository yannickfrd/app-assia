<?php

namespace App\Controller\Api;

use App\Service\Api\GoogleApi\GoogleCalendarApiService;
use Google\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GoogleCalendarController extends AbstractController
{
    /** @var GoogleCalendarApiService */
    private $gapiService;

    public function __construct(GoogleCalendarApiService $gapiService)
    {
        $this->gapiService = $gapiService;
    }

    /**
     * @Route("/auth-google-calendar", name="auth_google_calendar", methods={"GET"})
     * @throws Exception
     */
    public function authClientGoogleCalendar(Request $request): JsonResponse
    {
        $this->gapiService->setOnSessionRdvId($request->query->get('rdv_id'));

        return $this->json([
            'action' => 'create',
            'url' => $this->gapiService->getAuthUrl(),
        ]);
    }

    /**
     * @Route("/add-event-google-calendar", name="add_event_google_calendar")
     * @throws Exception
     */
    public function addEventGoogleCalendar(Request $request): RedirectResponse
    {
        $authCode = $request->query->get('code');

        if (!empty($authCode)) {
            $this->gapiService->authClient($authCode);
            $urlResponse = $this->gapiService->addRdv();

            return (empty($urlResponse)) ?
                $this->redirect($this->gapiService->getAuthUrl()) :
                $this->redirect($urlResponse);
        }

        return $this->redirect($this->gapiService->getAuthUrl());
    }

    /**
     * @Route("/google-event/{rdvId}/update-event-google-calendar", name="update_event_google_calendar", methods={"PUT"})
     * @throws Exception
     */
    public function updateEventGoogleCalendar(int $rdvId): JsonResponse
    {
        $updating = $this->gapiService->update($rdvId);

        if (!$updating) {
            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => 'Le RDV n\'a pas été mise à jour sur Google Agenda.',
            ]);
        }

        return $this->json([
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'Le RDV a bien été mise à jour sur Google Agenda.',
        ]);
    }

    /**
     * @Route("/google-event/{googleEventId}/delete-event-google-calendar", name="delete_event_google_calendar", methods={"DELETE"})
     * @param string $googleEventId
     * @return JsonResponse
     */
    public function deleteEventGoogleCalendar(string $googleEventId): JsonResponse
    {
        try {
            $this->gapiService->deleteEvent($googleEventId);
        } catch (Exception $e) {
            $getErrors = json_decode($e->getMessage(), false);

            if (0 < count($getErrors->error->errors)) {
                $messError = $getErrors->error->message;
                return $this->json([
                    'action' => 'delete',
                    'alert' => 'warning',
                    'msg' => 'Une erreur s\'est produite avec Google Agenda: "' . $messError . '".',
                ]);
            }
        }

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'Le RDV a bien été supprimé sur Google Agenda.',
        ]);
    }
}
