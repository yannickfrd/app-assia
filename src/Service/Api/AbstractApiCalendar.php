<?php

namespace App\Service\Api;

use App\Entity\Event\Rdv;
use App\Entity\Organization\User;
use App\Repository\Event\RdvRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractApiCalendar
{
    /** @var SessionInterface */
    protected $session;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var EntityManagerInterface */
    protected $em;

    /** @required */
    public function setSession(RequestStack $requestStack): ?SessionInterface
    {
        $previous = $this->session;
        $this->session = $requestStack->getSession();

        return $previous;
    }

    /** @required */
    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): ?UrlGeneratorInterface
    {
        $previous = $this->urlGenerator;
        $this->urlGenerator = $urlGenerator;

        return $previous;
    }

    /** @required */
    public function setEntityManager(EntityManagerInterface $em): ?EntityManagerInterface
    {
        $previous = $this->em;
        $this->em = $em;

        return $previous;
    }

    /**
     * Update th Rdv and set the event's Id Google|Outlook Calendar.
     */
    protected function setEventOnRdv(string $key, string $eventId): void
    {
        $setEventId = 'set'.ucfirst(strtolower($key)).'EventId';
        $rdv = $this->getRdv($key, $this->session->get(strtolower($key).'RdvId'))
            ->$setEventId($eventId);

        $this->em->persist($rdv);
        $this->em->flush();
    }

    /**
     * Returns the current Rdv recorded in session according to the id.
     */
    protected function getRdv(string $key, ?int $rdvId = null): Rdv
    {
        $id = $rdvId ?? $this->session->get(strtolower($key).'RdvId');

        /** @var RdvRepository $rdvRepo */
        $rdvRepo = $this->em->getRepository(Rdv::class);

        return $rdvRepo->findRdv($rdvId);
    }

    /**
     * Set the value of the option to true in the session,
     * because if it is passed through, it means that the user has selected the option.
     */
    public function setOnSessionRdvId(string $key, string $rdvId): void
    {
        $this->session->set('client'.ucfirst(strtolower($key)).'Checked', true);
        $this->session->set(strtolower($key).'RdvId', $rdvId);
    }

    /**
     * Create a Body for Google's or Outlook's event.
     */
    protected function createBodyEvent(string $rdvContent = null, User $rdvCreatedBy = null, string $rdvStatus = null): string
    {
        $body = $rdvContent.'<br>';
        $status = $rdvStatus ? '<br><strong>Statut : </strong>'.$rdvStatus : '';

        if ($rdvCreatedBy) {
            $body .= '<br><strong>Cr???? par : </strong>'.
                $this->em->getRepository(User::class)->find($rdvCreatedBy)->getFullname();
        }

        return $body.$status;
    }

    /**
     * Create an array with a \DateTime and a timeZone for Google's or Outlook's event.
     */
    protected function createDateEvent(\DateTimeInterface $dateTime): array
    {
        return [
            'dateTime' => $dateTime->format('c'),
            'timeZone' => $dateTime->getTimezone()->getName(),
        ];
    }

    /**
     * Create a Title for Google's or Outlook's event.
     */
    protected function createTitleEvent(Rdv $rdv): string
    {
        $title = $rdv->getTitle();

        if ($rdv->getSupportGroup()) {
            $fullName = $rdv->getSupportGroup()->getHeader()->getFullname();

            preg_match('/'.$fullName.'/', $rdv->getTitle(), $matches);

            if (empty($matches)) {
                $title .= ' | '.$fullName;
            }
        }

        return $title;
    }
}
