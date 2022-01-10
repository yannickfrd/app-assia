<?php

namespace App\Controller\Api;

use App\Service\Api\OutlookApi\OutlookCalendarApiService;
use GuzzleHttp\Exception\GuzzleException;
use Microsoft\Graph\Exception\GraphException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class OutlookCalendarController extends AbstractController
{
    /** @var OutlookCalendarApiService */
    private $outApiService;

    public function __construct(OutlookCalendarApiService $outApiService)
    {
        $this->outApiService = $outApiService;
    }

    /**
     * @Route("/auth-outlook-calendar", name="auth_outlook_calendar", methods={"GET"})
     * @param Request $request
     * @return JsonResponse
     */
    public function authClientOutlookCalendar(Request $request): JsonResponse
    {
        $this->outApiService->setOnSessionRdvId('outlook', $request->query->get('rdv_id'));

        return $this->json([
            'action' => 'create',
            'url' => $this->outApiService->getAuthUrl(),
        ]);
    }

    /**
     * @Route("/add-event-outlook-calendar", name="add_event_outlook_calendar")
     */
    public function addEventOutlookCalendar(Request $request): RedirectResponse
    {
        $authCode = $request->query->get('code');

        if (isset($authCode)) {
            $this->outApiService->authClient($authCode);

            $urlResponse = $this->outApiService->addRdv();

            return $this->redirect($urlResponse);
        }

        return $this->redirect($this->outApiService->getAuthUrl());
    }

    /**
     * @Route("/outlook-event-calendar/{rdvId}/update", name="update_outlook_event_calendar", methods={"PUT"})
     * @param int $rdvId
     * @return JsonResponse
     * @throws GuzzleException
     * @throws GraphException
     */
    public function updateEventOutlookCalendar(int $rdvId): JsonResponse
    {
        $updated = $this->outApiService->update($rdvId);

        if (!$updated) {
            return $this->json([
                'action' => 'update',
                'alert' => 'danger',
                'msg' => 'Le RDV n\'a pas été mise à jour sur Outlook Agenda.',
            ]);
        }

        return $this->json([
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'Le RDV a bien été mise à jour sur Outlook Agenda.',
        ]);
    }

    /**
     * @Route("/outlook-event-calendar/{eventId}/delete", name="delete_outlook_event_calendar", methods={"DELETE"})
     * @param string $eventId
     * @return JsonResponse
     * @throws GraphException
     * @throws GuzzleException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deleteEventOutlookCalendar(string $eventId): JsonResponse
    {
        $delete = $this->outApiService->delete($eventId);
        if (!$delete) {
            return $this->json([
                'action' => 'delete',
                'alert' => 'warning',
                'msg' => 'Une erreur s\'est produite avec Outlook Agenda.',
            ]);
        }

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'Le RDV a bien été supprimé sur Outlook Agenda.',
        ]);
    }
}
