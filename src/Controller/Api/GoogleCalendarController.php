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
     * @Route("/google-calendar/event/create", name="create_event_google_calendar", methods={"GET"})
     * @throws Exception
     */
    public function createEventGoogleCalendar(Request $request): JsonResponse
    {
        $this->gapiService->setOnSessionRdvId('google', $request->query->get('rdv_id'));

        return $this->json([
            'action' => 'create',
            'url' => $this->gapiService->getAuthUrl(),
        ]);
    }

    /**
     * Callback function
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
     * @Route("/google-calendar/event/update/{rdvId}", name="update_event_google_calendar", methods={"PUT"})
     * @throws Exception
     */
    public function updateEventGoogleCalendar(int $rdvId): JsonResponse
    {
        $updated = $this->gapiService->update($rdvId);

        if (!$updated) {
            return $this->json([
                'action' => 'update',
                'alert' => 'danger',
                'msg' => 'Le RDV n\'a pas été mise à jour sur Google Agenda.',
            ]);
        }

        if (is_string($updated)) {
            return $this->json([
                'action' => 'create',
                'url' => $updated,
            ]);
        }

        return $this->json([
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'Le RDV a bien été mise à jour sur Google Agenda.',
        ]);
    }

    /**
     * @Route("/google-calendar/event/delete/{eventId}", name="delete_event_google_calendar", methods={"DELETE"})
     * @param string $eventId
     * @return JsonResponse
     */
    public function deleteEventGoogleCalendar(string $eventId): JsonResponse
    {
        try {
            $this->gapiService->deleteEvent($eventId);
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
