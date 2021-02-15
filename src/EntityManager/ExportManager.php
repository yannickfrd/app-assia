<?php

namespace App\EntityManager;

use App\Entity\Admin\Export;
use App\Form\Admin\ExportSearchType;
use App\Entity\Support\SupportPerson;
use App\Form\Model\Admin\ExportSearch;
use App\Notification\ExportNotification;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use App\Service\Export\SupportPersonFullExport;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExportManager
{
    private $security;
    private $container;
    private $exportNotification;
    private $exportSupport;

    public function __construct(
        Security $security,
        ContainerInterface $container,
        ExportNotification $exportNotification,
        SupportPersonFullExport $exportSupport
    ) {
        $this->security = $security;
        $this->container = $container;
        $this->exportNotification = $exportNotification;
        $this->exportSupport = $exportSupport;
    }

    public function export(Request $request)
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

            $this->exportNotification->sendExport($user->getEmail(), $export);
        }
    }
}
