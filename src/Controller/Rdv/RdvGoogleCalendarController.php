<?php

namespace App\Controller\Rdv;

use App\Service\GoogleApi\GoogleAgenda;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RdvGoogleCalendarController extends AbstractController
{
    /**
     * @Route("/rdv/create-client-google-calendar", name="create_client_google_calendar", methods={"GET"})
     *
     * @param GoogleAgenda $googleAgenda
     * @return JsonResponse
     */
    public function createClientGoogleCalendar(GoogleAgenda $googleAgenda): JsonResponse
    {
        $url = $googleAgenda->sync();
        dd($url);
//        return $this->redirect($url);
        return $this->json(['url' => $url]);
    }
    /**
     * @Route("/rdv/new/response-google-agenda", name="response_google_agenda")
     */
    public function getResponseGoogleAgenda(Request $request, GoogleAgenda $googleAgenda)
    {
        $googleAgenda->getQuery($request->query->get('code'));
        // Si l'utilisateur approuve la demande d'accès, la réponse contient un code d'autorisation.
        // Si l'utilisateur n'approuve pas la demande, la réponse contient un message d'erreur.
        dump($request);
        $googleAgenda->getQuery($request->query);

    }
}
