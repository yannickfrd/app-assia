<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\Api\GoogleApi\GoogleCalendarApiService;
use Google\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class GoogleCalendarController extends AbstractController
{
    private $googleCalendar;

    public function __construct(GoogleCalendarApiService $googleCalendar)
    {
        $this->googleCalendar = $googleCalendar;
    }

    /**
     * @Route("/google-calendar/event/create", name="create_event_google_calendar", methods="GET")
     */
    public function createEventGoogleCalendar(Request $request): JsonResponse
    {
        $this->googleCalendar->setOnSessionRdvId('google', $request->query->get('rdvId'));

        return $this->json([
            'action' => 'create',
            'url' => $this->googleCalendar->getAuthUrl(),
        ]);
    }

    /**
     * Callback function.
     *
     * @Route("/add-event-google-calendar", name="add_event_google_calendar")
     */
    public function addEventGoogleCalendar(Request $request): RedirectResponse
    {
        $authCode = $request->query->get('code');

        if (!empty($authCode)) {
            $this->googleCalendar->authClient($authCode);
            $urlResponse = $this->googleCalendar->addRdv();

            return $this->redirect(empty($urlResponse) ? $this->googleCalendar->getAuthUrl() : $urlResponse);
        }

        return $this->redirect($this->googleCalendar->getAuthUrl());
    }

    /**
     * @Route("/google-calendar/event/update/{rdvId}", name="update_event_google_calendar", methods="PUT")
     */
    public function updateEventGoogleCalendar(int $rdvId): JsonResponse
    {
        $updated = $this->googleCalendar->update($rdvId);

        if (!$updated) {
            return $this->json([
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
     * @Route("/google-calendar/event/delete/{eventId}", name="delete_event_google_calendar", methods="DELETE")
     */
    public function deleteEventGoogleCalendar(string $eventId): JsonResponse
    {
        try {
            $this->googleCalendar->deleteEvent($eventId);
        } catch (Exception $e) {
            $getErrors = json_decode($e->getMessage(), false);

            if (0 < count($getErrors->error->errors)) {
                $messError = $getErrors->error->message;

                return $this->json([
                    'alert' => 'danger',
                    'msg' => 'Une erreur s\'est produite avec Google Agenda: "'.$messError.'".',
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
