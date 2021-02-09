<?php

namespace App\EventListener;

use App\Entity\Admin\Export;
use App\Entity\Organization\User;
use App\Entity\Support\SupportPerson;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Notification\MailNotification;
use App\Service\Export\SupportPersonFullExport;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class TerminateListener
{
    private $security;
    private $container;
    private $renderer;
    private $notification;
    private $exportSupport;

    public function __construct(
        Security $security,
        ContainerInterface $container,
        Environment $renderer,
        MailNotification $notification,
        SupportPersonFullExport $exportSupport
    ) {
        $this->security = $security;
        $this->container = $container;
        $this->renderer = $renderer;
        $this->notification = $notification;
        $this->exportSupport = $exportSupport;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        $this->updateLastActivity();

        switch ($route) {
            case 'export':
                $this->export($request);
                break;
        }
    }

    /**
     * Met à jour la date de dernière activité de l'utilisateur connecté.
     */
    protected function updateLastActivity(): void
    {
        /** @var User */
        $user = $this->security->getUser();
        if ($user && !$user->isActiveNow()) {
            $user->setLastActivityAt(new \DateTime());
            $this->container->get('doctrine')->getManager()->flush();
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
                ->setComment(substr(join(' <br/> ', $comment), 0, 255));

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
}
