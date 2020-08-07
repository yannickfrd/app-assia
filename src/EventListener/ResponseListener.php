<?php

namespace App\EventListener;

use Twig\Environment;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ResponseListener
{
    private $renderer;

    public function __construct(Environment $renderer)
    {
        $this->renderer = $renderer;
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();

        $serverName = $request->server->get('SERVER_NAME');

        if ($request->server->get('APP_ENV') != 'prod' || $serverName == '127.0.0.1:8000') {
            return $this->addBeta($event->getResponse());
        }
    }

    public function addBeta(Response $response): Response
    {
        $content = $response->getContent();

        $toastContent = $this->renderer->render('app/betaTest.html.twig');

        $content = str_replace(
          '</body>',
          $toastContent.'</body> ',
          $content
        );

        return $response->setContent($content);
    }
}
