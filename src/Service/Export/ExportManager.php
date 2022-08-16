<?php

namespace App\Service\Export;

use App\Entity\Admin\Export;
use App\Entity\Organization\User;
use App\Form\Model\Admin\ExportSearch;
use App\Form\Model\Support\SupportSearch;
use App\Notification\ExportNotification;
use App\Repository\Admin\ExportRepository;
use App\Repository\Support\SupportPersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ExportManager
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em,
        private ExportRepository $exportRepo,
        private ExportNotification $exportNotification,
        private SupportPersonRepository $supportPersonRepo,
        private EvaluationPersonExport $evaluationPersonExport,
        private ExportModelConverter $exportModelConverter
    ) {
    }

    /**
     * @param ExportSearch|SupportSearch $search
     */
    public function create(int $nbResults, object $search): ?Export
    {
        $lastExport = $this->exportRepo->findOneBy([
            'status' => Export::STATUS_IN_PROGRESS,
            'createdBy' => $this->security->getUser(),
        ], ['createdAt' => 'DESC']);

        if ($lastExport && $lastExport->getCreatedAt()->modify('+10 minutes') > new \DateTime()) {
            return null;
        }

        $export = (new Export())
            ->setTitle('Export des suivis')
            ->setStatus(Export::STATUS_IN_PROGRESS)
            ->setNbResults($nbResults)
            ->setComment($this->getCommentExport($search))
        ;

        $this->em->persist($export);
        $this->em->flush();

        return $export;
    }

    public function send(Export $export, ExportSearch $search): ?Export
    {
        $supports = $this->supportPersonRepo->findSupportsFullToExport($search);

        if (array_key_exists($search->getModel(), ExportSearch::MODELS)) {
            $file = $this->evaluationPersonExport->exportData($supports, $search);
        } else {
            $file = $this->exportModelConverter->exportData($supports, $search);
        }

        $this->update($export, $file);

        /** @var User */
        $user = $this->security->getUser();

        $this->exportNotification->sendExport($user->getEmail(), $export);

        return $export;
    }

    private function update(Export $export, string $file): void
    {
        $export
            ->setFileName($file)
            ->setStatus(Export::STATUS_TERMINATE)
            ->setSize(filesize($file))
            ->setUsedMemory(round(memory_get_usage() / 1_000_000))
        ;

        $this->em->flush();
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
        $comment[] = 'Intervenant(s) : '.($search->getReferentsToString() ? join(', ', $search->getReferentsToString()) : 'tous');
        $comment[] = 'Service(s) : '.($search->getServicesToString() ? join(', ', $search->getServicesToString()) : 'tous');
        $comment[] = 'Dispositif(s) : '.($search->getDevicesToString() ? join(', ', $search->getDevicesToString()) : 'tous');

        return substr(join(' <br/> ', $comment), 0, 255);
    }
}
