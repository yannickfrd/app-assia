<?php

namespace App\EventListener;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

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
            $event->setResponse(new Response($this->getView(), Response::HTTP_SERVICE_UNAVAILABLE));
        }
    }

    protected function getView()
    {
        return $this->renderer->render("bundles/TwigBundle/Exception/maintenance.html.twig");
    }
}
