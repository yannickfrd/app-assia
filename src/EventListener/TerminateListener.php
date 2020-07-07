<?php

namespace App\EventListener;

use Twig\Environment;
use App\Entity\Export;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Form\Model\ExportSearch;
use App\Form\Export\ExportSearchType;
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
            // case 'support_evaluation_show':
            //     $this->editEvaluation($request);
            //     break;
            // case 'support_evaluation_edit':
            //     $this->editEvaluation($request);
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

        $search = new ExportSearch();

        $form = ($this->container->get('form.factory')->create(ExportSearchType::class, $search)) // FormFactoryInterface $formFactory
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->container->get('doctrine')->getManager();
            $repo = $manager->getRepository(SupportPerson::class);
            $supports = $repo->findSupportsFullToExport($search);

            $file = $this->exportSupport->exportData($supports);

            $comment = [];

            $comment[] = 'Statut : '.($search->getStatus() ? join(', ', $search->getStatusToString()) : 'tous');
            $search->getSupportDates() ? $comment[] = $search->getSupportDatesToString() : null;
            $search->getStart() ? $comment[] = 'Début : '.$search->getStart()->format('d/m/Y') : null;
            $search->getEnd() ? $comment[] = 'Fin : '.$search->getEnd()->format('d/m/Y') : null;
            $comment[] = 'Référent(s) : '.($search->getReferentsToString() ? join(', ', $search->getReferentsToString()) : 'tous');
            $comment[] = 'Service(s) : '.($search->getServicesToString() ? join(', ', $search->getServicesToString()) : 'tous');
            $comment[] = 'Dispositif(s) : '.($search->getDevicesToString() ? join(', ', $search->getDevicesToString()) : 'tous');

            $export = (new Export())
                ->setTitle('Export des suivis')
                ->setFileName($file)
                ->setSize(filesize($file))
                ->setNbResults(count($supports))
                ->setComment(join(' <br/> ', $comment));

            $manager->persist($export);
            $manager->flush();

            $htmlBody = $this->renderer->render(
                    'emails/exportFileEmail.html.twig',
                    ['export' => $export]
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
            $manager = $this->container->get('doctrine')->getManager();

            $repo = $manager->getRepository(SupportGroup::class);
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
