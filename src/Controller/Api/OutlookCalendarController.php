<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\Api\OutlookApi\OutlookCalendarApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OutlookCalendarController extends AbstractController
{
    private $outlookCalendar;

    public function __construct(OutlookCalendarApiService $outlookCalendar)
    {
        $this->outlookCalendar = $outlookCalendar;
    }

    /**
     * @Route("/outlook-calendar/event/create", name="create_event_outlook_calendar", methods="GET")
     */
    public function createEventClientOutlookCalendar(Request $request): JsonResponse
    {
        $this->outlookCalendar->setOnSessionRdvId('outlook', $request->query->get('rdvId'));

        return $this->json([
            'action' => 'create',
            'url' => $this->outlookCalendar->getAuthUrl(),
        ]);
    }

    /**
     * Callback function.
     *
     * @Route("/add-event-outlook-calendar", name="add_event_outlook_calendar")
     */
    public function addEventOutlookCalendar(Request $request): RedirectResponse
    {
        $authCode = $request->query->get('code');

        if (isset($authCode)) {
            $this->outlookCalendar->authClient($authCode);

            $urlResponse = $this->outlookCalendar->addRdv();

            return $this->redirect($urlResponse);
        }

        return $this->redirect($this->outlookCalendar->getAuthUrl());
    }

    /**
     * @Route("/outlook-calendar/event/update/{rdvId}", name="update_event_outlook_calendar", methods="PUT")
     */
    public function updateEventOutlookCalendar(TranslatorInterface $translator, int $rdvId): JsonResponse
    {
        $updated = $this->outlookCalendar->update($rdvId);

        if (!$updated) {
            return $this->json([
                'alert' => 'danger',
                'msg' => $translator->trans('outlook_calendar.error_occurred', [], 'app'),
            ]);
        }

        return $this->json([
            'action' => 'update',
            'alert' => 'success',
            'msg' => $translator->trans('outlook_calendar.updated_successfully', [], 'app'),
        ]);
    }

    /**
     * @Route("/outlook-calendar/event/delete/{eventId}", name="delete_event_outlook_calendar", methods="DELETE")
     */
    public function deleteEventOutlookCalendar(TranslatorInterface $translator, string $eventId): JsonResponse
    {
        $delete = $this->outlookCalendar->delete($eventId);
        if (!$delete) {
            return $this->json([
                'alert' => 'danger',
                'msg' => $translator->trans('outlook_calendar.error_occurred', [], 'app'),
            ]);
        }

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $translator->trans('outlook_calendar.deleted_successfully', [], 'app'),
        ]);
    }
}
