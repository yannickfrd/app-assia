<?php

namespace App\Controller\Rdv;

use App\Entity\Support\Rdv;
use App\Form\Support\Rdv\RdvType;
use App\Repository\Support\RdvRepository;
use App\Service\Calendar;
use App\Service\SupportGroup\SupportManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    private $rdvRepo;

    public function __construct(RdvRepository $rdvRepo)
    {
        $this->rdvRepo = $rdvRepo;
    }

    /**
     * Affiche l'agenda de l'utilisateur (vue mensuelle).
     *
     * @Route("/calendar/month/{year}/{month}", name="calendar_show", methods="GET", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * })
     * @Route("/calendar/month", name="calendar", methods="GET")
     */
    public function showCalendar(int $year = null, int $month = null): Response
    {
        $rdv = (new Rdv())->addUser($this->getUser());
        $form = $this->createForm(RdvType::class, $rdv);

        return $this->render('app/rdv/calendar.html.twig', [
            'calendar' => $calendar = new Calendar($year, $month),
            'form_rdv' => $form->createView(),
            'rdvs' => $this->rdvRepo->findRdvsBetweenByDay(
                $calendar->getFirstMonday(),
                $calendar->getLastday(),
                null,
                $this->getUser()
            ),
        ]);
    }

    /**
     * Affiche l'agenda d'un suivi (vue mensuelle).
     *
     * @Route("/support/{id}/calendar/month/{year}/{month}", name="support_calendar_show", methods="GET", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * })
     * @Route("/support/{id}/calendar/month", name="support_calendar", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function showSupportCalendar(int $id, SupportManager $supportManager, $year = null, $month = null): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $form = $this->createForm(RdvType::class, new Rdv(), [
            'support_group' => $supportGroup,
        ]);

        return $this->render('app/rdv/calendar.html.twig', [
            'support' => $supportGroup,
            'calendar' => $calendar = new Calendar($year, $month),
            'form_rdv' => $form->createView(),
            'rdvs' => $this->rdvRepo->findRdvsBetweenByDay(
                $calendar->getFirstMonday(),
                $calendar->getLastday(),
                $supportGroup
            ),
        ]);
    }
}
