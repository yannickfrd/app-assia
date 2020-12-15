<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Twig\Environment;

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
        $response = $event->getResponse();

        $server = $request->server;

        if ($response->getContent() && ($server->get('APP_ENV') != 'prod' || $server->get('SERVER_NAME') === '127.0.0.1:8000')) {
            return $this->addBeta($response);
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
