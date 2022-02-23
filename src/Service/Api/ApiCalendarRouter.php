<?php

namespace App\Service\Api;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ApiCalendarRouter
{
    /** @var ContainerInterface */
    private $router;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('router');
    }

    public function getUrls(string $action, int $rdvId, array $requestRdv = [], array $eventIds = []): array
    {
        $params = [];
        $urls = [];

        switch ($action) {
            case 'update':
            case 'create':
                $params['rdvId'] = $rdvId;
                break;
            case 'delete':
                foreach ($eventIds as $apiName => $eventId) {
                    if (null !== $eventId) {
                        $urls[$apiName] = $this->router->generate($action.'_event_'.$apiName.'_calendar', [
                            'eventId' => $eventId,
                            ]
                        );
                    }
                }

                return $urls;
        }

        if (isset($requestRdv['_googleCalendar']) && (bool) $requestRdv['_googleCalendar']) {
            $urls['google'] = $this->router->generate($action.'_event_google_calendar', $params);
        }
        if (isset($requestRdv['_outlookCalendar']) && (bool) $requestRdv['_outlookCalendar']) {
            $urls['outlook'] = $this->router->generate($action.'_event_outlook_calendar', $params);
        }

        return $urls;
    }
}
