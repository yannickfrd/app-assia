<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener
{
    protected $renderer;
    protected $maintenance;

    public function __construct(Environment $renderer, bool $maintenance)
    {
        $this->maintenance = $maintenance;
        $this->renderer = $renderer;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($this->maintenance) {
            $event->setResponse(
                new Response(
                    $this->renderer->render('bundles/TwigBundle/Exception/maintenance.html.twig'),
                    Response::HTTP_SERVICE_UNAVAILABLE
                )
            );
        }
    }
}
