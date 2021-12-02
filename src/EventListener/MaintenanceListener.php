<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MaintenanceListener
{
    protected $renderer;
    protected $maintenance;

    public function __construct(Environment $renderer, bool $maintenance = false)
    {
        $this->renderer = $renderer;
        $this->maintenance = $maintenance;
    }

    public function onKernelRequest(RequestEvent $event): void
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
