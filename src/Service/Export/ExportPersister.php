<?php

namespace App\Service\Export;

use App\Entity\Admin\Export;
use App\Form\Model\Admin\ExportSearch;
use App\Form\Model\Support\SupportSearch;
use App\Notification\ExportNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ExportPersister
{
    private $security;
    private $manager;
    private $exportNotification;

    public function __construct(Security $security, EntityManagerInterface $manager, ExportNotification $exportNotification)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->exportNotification = $exportNotification;
    }

    /**
     * @param ExportSearch|SupportSearch $search
     */
    public function save($file, $supports, $search): Export
    {
        $export = (new Export())
            ->setTitle('Export des suivis')
            ->setFileName($file)
            ->setSize(filesize($file))
            ->setNbResults(count($supports))
            ->setComment($this->getCommentExport($search));

        $this->manager->persist($export);
        $this->manager->flush();

        /** @var User */
        $user = $this->security->getUser();

        $this->exportNotification->sendExport($user->getEmail(), $export);

        return $export;
    }

    /**
     * @param ExportSearch|SupportSearch $search
     */
    private function getCommentExport($search): string
    {
        $comment = [];
        $comment[] = 'Statut : '.($search->getStatus() ? join(', ', $search->getStatusToString()) : 'tous');
        $search->getSupportDates() ? $comment[] = $search->getSupportDatesToString() : null;
        $search->getStart() ? $comment[] = 'Début : '.$search->getStart()->format('d/m/Y') : null;
        $search->getEnd() ? $comment[] = 'Fin : '.$search->getEnd()->format('d/m/Y') : null;
        $comment[] = 'Référent(s) : '.($search->getReferentsToString() ? join(', ', $search->getReferentsToString()) : 'tous');
        $comment[] = 'Service(s) : '.($search->getServicesToString() ? join(', ', $search->getServicesToString()) : 'tous');
        $comment[] = 'Dispositif(s) : '.($search->getDevicesToString() ? join(', ', $search->getDevicesToString()) : 'tous');

        return substr(join(' <br/> ', $comment), 0, 255);
    }
}
