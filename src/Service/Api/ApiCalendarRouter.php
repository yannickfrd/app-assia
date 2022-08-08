<?php

namespace App\Service\Api;

use App\Entity\Event\Rdv;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiCalendarRouter
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public function getUrls(string $action, Rdv $rdv): array
    {
        $urls = [];

        if('delete' === $action) {
            if (null !== $rdv->getGoogleEventId()) {
                $urls['google'] = $this->router->generate('delete_event_google_calendar', [
                    'eventId' => $rdv->getGoogleEventId(),
                ]);
            }
            if (null !== $rdv->getOutlookEventId()) {
                $urls['outlook'] = $this->router->generate('delete_event_outlook_calendar', [
                    'eventId' => $rdv->getOutlookEventId(),
                ]);
            }

            return $urls;
        }

        if (true === $rdv->getGoogleCalendar()) {
            $urls['google'] = $this->router->generate($action.'_event_google_calendar', ['rdvId' => $rdv->getId()]);
        }

        if (true === $rdv->getOutlookCalendar()) {
            $urls['outlook'] = $this->router->generate($action.'_event_outlook_calendar', ['rdvId' => $rdv->getId()]);
        }

        return $urls;
    }
}
