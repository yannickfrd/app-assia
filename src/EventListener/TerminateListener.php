<?php

namespace App\EventListener;

use App\Entity\SupportGroup;
use Twig\Environment;
use App\Form\Model\Export;
use App\Entity\SupportPerson;
use App\Form\Export\ExportType;
use App\Notification\MailNotification;
use App\Export\SupportPersonFullExport;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TerminateListener
{
    private $security;
    private $container;
    private $renderer;
    private $notification;
    private $exportSupport;
    private $evaluationListener;

    public function __construct(
        Security $security,
        ContainerInterface $container,
        Environment $renderer,
        MailNotification $notification,
        SupportPersonFullExport $exportSupport,
        bool $evaluationListener
    ) {
        $this->security = $security;
        $this->container = $container;
        $this->renderer = $renderer;
        $this->notification = $notification;
        $this->exportSupport = $exportSupport;
        $this->evaluationListener = $evaluationListener;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        $request = $event->getRequest();
        // $response = $event->getResponse();
        $route = $request->attributes->get('_route');

        // if (!$event->isMasterRequest()) {
        //     return;
        // }

        switch ($route) {
            case 'export':
                $this->export($request);
                break;
            case 'support_evaluation_show':
                $this->editEvaluation($request);
                break;
            case 'support_evaluation_edit':
                $this->editEvaluation($request);
                break;
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
                    ['path' => $this->exportSupport->exportData($supports)]
                );

            $this->notification->send(
                    ['email' => $user->getEmail(), 'name' => $user->getFirstname()],
                    'Esperer95.app | Export de données',
                    $htmlBody,
                );
        }
    }

    public function editEvaluation($request)
    {
        if (!$this->evaluationListener) {
            return;
        }

        $evaluationGroup = $request->request->get('evaluation');

        if ($evaluationGroup) {
            $entityManager = $this->container->get('doctrine')->getManager();

            $repo = $entityManager->getRepository(SupportGroup::class);
            $supportId = $request->attributes->get('id');
            $supportGroup = $repo->findSupportById($supportId);

            $fullnamePerson = '';

            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if ($supportPerson->getHead()) {
                    $fullnamePerson = $supportPerson->getPerson()->getFullname();
                }
            }

            $htmlBody = $this->renderer->render(
                'emails/EvaluationEmail.html.twig',
                [
                    'supportId' => $supportId,
                    'fullnamePerson' => $fullnamePerson,
                    'evaluation' => $evaluationGroup,
                ]
            );

            $this->notification->send(
                ['email' => ('romain.madelaine@esperer-95.org'), 'name' => 'Admin'],
                'Esperer95.app | Evaluation enregistrée : '.$fullnamePerson.' ('.$supportId.')',
                $htmlBody,
            );
        }
    }
}
