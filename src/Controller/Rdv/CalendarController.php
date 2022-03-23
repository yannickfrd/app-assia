<?php

declare(strict_types=1);

namespace App\Controller\Rdv;

use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use App\Form\Support\Rdv\RdvType;
use App\Repository\Support\RdvRepository;
use App\Service\Calendar;
use App\Service\SupportGroup\SupportManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CalendarController extends AbstractController
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
        $rdv = (new Rdv())->setUser($this->getUser());
        $form = $this->createForm(RdvType::class, $rdv);

        return $this->render('app/rdv/calendar.html.twig', [
            'calendar' => $calendar = new Calendar($year, $month),
            'form' => $form->createView(),
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
    public function showSupportCalendar(int $id, int $year = null, int $month = null, SupportManager $supportManager): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(RdvType::class, (new Rdv())->setUser($user)->setSupportGroup($supportGroup));

        return $this->render('app/rdv/calendar.html.twig', [
            'support' => $supportGroup,
            'calendar' => $calendar = new Calendar($year, $month),
            'form' => $form->createView(),
            'rdvs' => $this->rdvRepo->findRdvsBetweenByDay(
                $calendar->getFirstMonday(),
                $calendar->getLastday(),
                $supportGroup
            ),
        ]);
    }

    /**
     * Affiche un jour du mois (vue journalière).
     *
     * @Route("/calendar/day/{year}/{month}/{day}", name="calendar_day_show", methods="GET", requirements={
     * "year" : "\d{4}",
     * "month" : "0?[1-9]|1[0-2]",
     * "day" : "[1-9]|[0-3][0-9]",
     * })
     */
    public function showDay(int $year = null, int $month = null, int $day = null, Request $request): Response
    {
        $startDay = new \DateTime($year.'-'.$month.'-'.$day);
        $endDay = clone ($startDay)->modify('+1 day');

        $form = $this->createForm(RdvType::class, new Rdv())
            ->handleRequest($request);

        return $this->render('app/rdv/day.html.twig', [
            'form' => $form->createView(),
            'rdvs' => $this->rdvRepo->findRdvsBetween($startDay, $endDay, null, $this->getUser()),
        ]);
    }
}
