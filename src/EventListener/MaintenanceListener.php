<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener
{
    protected $renderer;
    protected $maintenance;
    protected $appVersion;

    public function __construct(Environment $renderer, bool $maintenance = false, string $appVersion = 'prod')
    {
        $this->renderer = $renderer;
        $this->maintenance = $maintenance;
        $this->appVersion = $appVersion;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->maintenance && $this->appVersion === 'prod') {
            $event->setResponse(
                new Response(
                    $this->renderer->render('bundles/TwigBundle/Exception/maintenance.html.twig'),
                    Response::HTTP_SERVICE_UNAVAILABLE
                )
            );
        }
    }
}
