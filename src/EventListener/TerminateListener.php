<?php

namespace App\EventListener;

use Twig\Environment;
use App\Form\Model\Export;
use App\Entity\SupportPerson;
use App\Form\Export\ExportType;
use App\Form\Model\UserResetPassword;
use App\Notification\MailNotification;
use App\Export\SupportPersonFullExport;
use App\Form\Security\ForgotPasswordType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TerminateListener
{
    private $security;
    private $container;
    private $exportSupport;
    private $renderer;
    private $notification;

    public function __construct(
        Security $security,
        ContainerInterface $container,
        Environment $renderer,
        MailNotification $notification,
        SupportPersonFullExport $exportSupport)
    {
        $this->security = $security;
        $this->container = $container;
        $this->exportSupport = $exportSupport;
        $this->renderer = $renderer;
        $this->notification = $notification;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $route = $request->attributes->get('_route');

        if (!$event->isMasterRequest()) {
            return;
        }

        switch ($route) {
            case 'export':
                $this->export($request);
            break;
            case 'security_forgot_password':
                $this->reinitPassword($request);
            break;
            // case '_wdt':
            //     return;
            //     break;
            default:
                return;
                break;
        }
    }

    protected function export($request)
    {
        /** @var User */
        $user = $this->security->getUser();

        $export = new Export();

        $form = ($this->container->get('form.factory')->create(ExportType::class, $export)) // FormFactoryInterface $formFactory
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->container->get('doctrine')->getManager();
            $repo = $entityManager->getRepository(SupportPerson::class);
            $supports = $repo->findSupportsFullToExport($export);

            $htmlBody = $this->renderer->render(
                    'emails/exportFileEmail.html.twig',
                    [
                        'user' => $user,
                        'path' => $this->exportSupport->exportData($supports),
                    ]
                );

            $this->notification->send(
                    ['email' => $user->getEmail(), 'name' => $user->getFirstname()],
                    'Esperer 95 | Export de données',
                    $htmlBody,
                );
        }
    }

    protected function reinitPassword($request)
    {
        // $userResetPassword = new UserResetPassword();

        // $form = ($this->container->get('form.factory')->create(ForgotPasswordType::class, $userResetPassword)) // FormFactoryInterface $formFactory
        //     ->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {

        //     $user->setToken(bin2hex(random_bytes(32))) // Enregistre le token dans la base
        //         ->setTokenCreatedAt(new \DateTime());

        //     $this->manager->flush();

        //     $message = $this->notification->reinitPassword($user); // Envoie l'email
        // }
    }
}
