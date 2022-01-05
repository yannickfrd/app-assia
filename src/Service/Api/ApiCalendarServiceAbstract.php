<?php

namespace App\Service\Api;

use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class ApiCalendarServiceAbstract
{
    protected const CLIENT_GOOGLE_CHECKED = 'clientGoogleChecked';
    protected const CLIENT_OUTLOOK_CHECKED = 'clientOutlookChecked';

    protected const GOOGLE_RDV_ID = 'googleRdvId';
    protected const OUTLOOK_RDV_ID = 'outlookRdvId';

    /** @var SessionInterface */
    protected $session;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var EntityManagerInterface */
    protected $em;

    /** @required */
    public function setSession(SessionInterface $session): ?SessionInterface
    {
        $previous = $this->session;
        $this->session = $session;

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
     * Update th Rdv and set the event's Id Google|Outlook Calendar
     * @param string $key
     * @param string $eventId
     */
    protected function setEventOnRdv(string $key, string $eventId): void
    {
        $setEventId = 'set' . ucfirst(strtolower($key)) . 'EventId';
        $rdv = $this->getRdv($this->session->get(strtolower($key) . 'RdvId'))
            ->$setEventId($eventId);

        $this->em->persist($rdv);
        $this->em->flush();
    }

    /**
     * Returns the current Rdv recorded in session according to the id
     * @param $rdvId
     * @return Rdv
     */
    protected function getRdv($rdvId = null): Rdv
    {
        $id = $rdvId ?? $this->session->get(self::GOOGLE_RDV_ID);
        return $this->em->getRepository(Rdv::class)->find($id);
    }


    /**
     * Set the value of the option to true in the session,
     * because if it is passed through, it means that the user has selected the option.
     * @param string $key
     * @param string $rdvId
     */
    public function setOnSessionRdvId(string $key, string $rdvId): void
    {
        $this->session->set('client' . ucfirst(strtolower($key)) . 'Checked', true);
        $this->session->set(strtolower($key) . 'RdvId', $rdvId);
    }

    /**
     * Create a Body for Google's or Outlook's event
     * @param string|null $rdvContent
     * @param User|null $rdvCreatedBy
     * @param string|null $rdvStatus
     * @return string
     */
    protected function createBodyEvent(string $rdvContent = null, User $rdvCreatedBy = null, string $rdvStatus = null): string
    {
        $body = $rdvContent;
        $status = $rdvStatus ? '<br><strong>Statut : </strong>' . $rdvStatus : '';
        if ($rdvCreatedBy) {
            $body .= '<br><strong>Créé par : </strong>' .
                $this->em->getRepository(User::class)->find($rdvCreatedBy)->getFullname();
        }

        return $body . $status;
    }

    /**
     * Create an array with a \DateTime and a timeZone for Google's or Outlook's event
     * @param DateTimeInterface $dateTime
     * @return array
     */
    protected function createDateEvent(DateTimeInterface $dateTime): array
    {
        return [
            'dateTime' => $dateTime,
            'timeZone' => $dateTime->getTimezone()->getName()
        ];
    }

    /**
     * Create a Title for Google's or Outlook's event
     * @param Rdv $rdv
     * @return string
     */
    protected function createTitleEvent(Rdv $rdv): string
    {
        $title = $rdv->getTitle();
        if ($rdv->getSupportGroup()) {
            $pattern = '/' . $rdv->getSupportGroup()->getHeader()->getFullname() . '/';
            preg_match($pattern, $rdv->getTitle(), $matches);

            if (empty($matches)) {
                $title .= ' | ' . $rdv->getSupportGroup()->getHeader()->getFullname();
            }
        }

        return $title;
    }

}