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

        if ($response->getContent() && ('prod' != $server->get('APP_VERSION') || '127.0.0.1:8000' === $server->get('SERVER_NAME'))) {
            return $this->addPopUp($response);
        }
    }

    public function addPopUp(Response $response): Response
    {
        $content = $response->getContent();

        $toastContent = $this->renderer->render('_shared/_betaTest.html.twig');

        $content = str_replace(
          '</body>',
          $toastContent.'</body> ',
          $content
        );

        return $response->setContent($content);
    }
}
