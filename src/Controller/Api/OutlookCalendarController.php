<?php

namespace App\Controller\Api;

use App\Service\Api\OutlookApi\OutlookCalendarApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OutlookCalendarController extends AbstractController
{
    /**
     * @var OutlookCalendarApiService
     */
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

        return $this->redirect($this->gapiService->getAuthUrl());
    }
//
//    /**
//     * @Route("/google-event/{checked}/{rdvId}/update-event-google-calendar", name="update_event_google_calendar", methods={"PUT"})
//     * @throws Exception
//     */
//    public function updateEventGoogleCalendar(bool $checked, int $rdvId): JsonResponse
//    {
//        if (!$checked) {
//            $this->gapiService->removeChecked();
//
//            return $this->json([
//                'action' => 'update',
//                'alert' => 'success',
//                'msg' => 'L\'option "Google Agenda" a bien été désactivé.'
//            ]);
//        }
//
//        $updating = $this->gapiService->update($rdvId);
//
//        if (!$updating) {
//            return $this->json([
//                'action' => 'update',
//                'alert' => 'success',
//                'msg' => 'Le RDV n\'a pas été mise à jour sur Google Agenda.',
//            ]);
//        }
//
//        return $this->json([
//            'action' => 'update',
//            'alert' => 'success',
//            'msg' => 'Le RDV a bien été mise à jour sur Google Agenda.',
//        ]);
//    }
//
//    /**
//     * @Route("/google-event/{googleEventId}/delete-event-google-calendar", name="delete_event_google_calendar", methods={"DELETE"})
//     * @param string $googleEventId
//     * @return JsonResponse
//     */
//    public function deleteEventGoogleCalendar(string $googleEventId): JsonResponse
//    {
//        try {
//            $this->gapiService->deleteEvent($googleEventId);
//        } catch (Exception $e) {
//            $getErrors = json_decode($e->getMessage(), false);
//
//            if (0 < count($getErrors->error->errors)) {
//                $messError = $getErrors->error->message;
//                return $this->json([
//                    'action' => 'delete',
//                    'alert' => 'warning',
//                    'msg' => 'Une erreur s\'est produite avec Google Agenda: "' . $messError . '".',
//                ]);
//            }
//        }
//
//        return $this->json([
//            'action' => 'delete',
//            'alert' => 'warning',
//            'msg' => 'Le RDV a bien été supprimé sur Google Agenda.',
//        ]);
//    }
}
