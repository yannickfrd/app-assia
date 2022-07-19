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
use Symfony\Contracts\Translation\TranslatorInterface;

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
    public function updateEventGoogleCalendar(TranslatorInterface $translator, int $rdvId): JsonResponse
    {
        $updated = $this->googleCalendar->update($rdvId);

        if (!$updated) {
            return $this->json([
                'alert' => 'danger',
                'msg' => $translator->trans('google_calendar.error_occurred', [], 'app'),
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
            'msg' => $translator->trans('google_calendar.updated_successfully', [], 'app'),
        ]);
    }

    /**
     * @Route("/google-calendar/event/delete/{eventId}", name="delete_event_google_calendar", methods="DELETE")
     */
    public function deleteEventGoogleCalendar(TranslatorInterface $translator, string $eventId): JsonResponse
    {
        try {
            $this->googleCalendar->deleteEvent($eventId);
        } catch (Exception $e) {
            $getErrors = json_decode($e->getMessage(), false);

            if (0 < count($getErrors->error->errors)) {
                $errrorMsg = $getErrors->error->message;

                return $this->json([
                    'alert' => 'danger',
                    'msg' => $translator->trans('google_calendar.error_occurred_with_msg', ['msg' => $errrorMsg], 'app'),
                ]);
            }
        }

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $translator->trans('google_calendar.deleted_successfully', [], 'app'),
        ]);
    }
}
