<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Entity\Admin\Setting;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Organization\Place;
use App\Entity\Organization\Referent;
use App\Entity\Organization\UserConnection;
use App\Entity\People\RolePerson;
use App\Entity\Support\Document;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Repository\Admin\SettingRepository;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Evaluation\EvaluationPersonRepository;
use App\Repository\Event\RdvRepository;
use App\Repository\Event\TaskRepository;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\ReferentRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\UserConnectionRepository;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\People\PersonRepository;
use App\Repository\People\RolePersonRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\PlaceGroupRepository;
use App\Repository\Support\PlacePersonRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\DoctrineTrait;
use App\Service\SupportGroup\SupportAnonymizer;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Timestampable\TimestampableListener;

final class Archiver
{
    use DoctrineTrait;

    /**
     * Delay for the removal of PeopleGroup or people without SupportGroup.
     * This number corresponds to the number of months.
     */
    private EntityManagerInterface $em;

    private SettingRepository $settingRepo;
    private DocumentRepository $documentRepo;
    private NoteRepository $noteRepo;
    private PaymentRepository $paymentRepo;
    private PeopleGroupRepository $peopleGroupRepo;
    private PersonRepository $personRepo;
    private RdvRepository $rdvRepo;
    private ServiceRepository $serviceRepo;
    private SupportGroupRepository $supportGroupRepo;
    private TaskRepository $taskRepo;

    private SupportAnonymizer $supportAnonymizer;

    private ?array $servicesDelayArchiveDates = null;
    private ?Setting $defaultSetting = null;
    private ?array $countSupportGroupElements = null;
    private string $documentsDirectory;

    private ?array $peopleGroupWithoutSupportGroup = null;
    private ?array $peopleWithoutSupportGroup = null;

    private array $supportGroupsToArchive = [];
    private array $supportGroupsToPurge = [];
    private array $peopleGroupIdsToArchive = [];
    private array $peopleIdsToArchive = [];

    private int $nbDocumentsToArchive = 0;
    private int $nbNotesToArchive = 0;
    private int $nbPaymentsToArchive = 0;
    private int $nbRdvsToArchive = 0;
    private int $nbTasksToArchive = 0;

    private \DateTime $now;
    private \DateTime $defaultArchiveDate;
    private \DateTime $limitDate;

    public function __construct(
        EntityManagerInterface $em,
        DocumentRepository $documentRepo,
        NoteRepository $noteRepo,
        PeopleGroupRepository $peopleGroupRepo,
        PersonRepository $personRepo,
        RdvRepository $rdvRepo,
        PaymentRepository $paymentRepo,
        ServiceRepository $serviceRepo,
        SettingRepository $settingRepo,
        SupportGroupRepository $supportGroupRepo,
        TaskRepository $taskRepo,
        SupportAnonymizer $supportAnonymizer,
        string $documentsDirectory
    ) {
        $this->em = $em;

        $this->documentRepo = $documentRepo;
        $this->noteRepo = $noteRepo;
        $this->paymentRepo = $paymentRepo;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->personRepo = $personRepo;
        $this->rdvRepo = $rdvRepo;
        $this->settingRepo = $settingRepo;
        $this->serviceRepo = $serviceRepo;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->taskRepo = $taskRepo;

        $this->supportAnonymizer = $supportAnonymizer;

        $this->documentsDirectory = $documentsDirectory;

        $this->defaultSetting = $this->settingRepo->findOneBy([]) ?? new Setting();

        $this->now = new \DateTime();
        $this->defaultArchiveDate = (new \DateTime())->modify('-'.$this->defaultSetting->getSoftDeletionDelay().' months');
        $this->limitDate = (new \DateTime())->modify('-'.$this->defaultSetting->getHardDeletionDelay().' months');
    }

    public function getStats(): array
    {
        $this->initDatas();

        $this->disableFilter($this->em, 'softdeleteable');

        return [
            'support_groups' => [
                'archive_ids' => $this->supportGroupsToArchive,
                'purge_ids' => $this->supportGroupsToPurge,
                'archive_count' => count($this->supportGroupsToArchive),
                'purge_count' => $this->supportGroupRepo->countDeletedObjects($this->limitDate),
            ],
            'people_groups' => [
                'archive_count' => count(array_unique($this->peopleGroupIdsToArchive)),
                'purge_count' => $this->peopleGroupRepo->countDeletedObjects($this->limitDate)
                    + count($this->peopleGroupRepo->findPeopleGroupWithoutSupportGroup($this->limitDate)),
            ],
            'people' => [
                'archive_count' => count(array_unique($this->peopleIdsToArchive)),
                'purge_count' => $this->personRepo->countDeletedObjects($this->limitDate)
                    + count($this->personRepo->findPeopleWithoutSupport($this->limitDate)),
            ],
            'documents' => [
                'archive_count' => $this->nbDocumentsToArchive,
                'purge_count' => $this->documentRepo->countDeletedObjects($this->limitDate),
            ],
            'notes' => [
                'archive_count' => $this->nbNotesToArchive,
                'purge_count' => $this->noteRepo->countDeletedObjects($this->limitDate),
            ],
            'payments' => [
                'archive_count' => $this->nbPaymentsToArchive,
                'purge_count' => $this->paymentRepo->countDeletedObjects($this->limitDate),
            ],
            'rdvs' => [
                'archive_count' => $this->nbRdvsToArchive,
                'purge_count' => $this->rdvRepo->countDeletedObjects($this->limitDate),
            ],
            'tasks' => [
                'archive_count' => $this->nbTasksToArchive,
                'purge_count' => $this->taskRepo->countDeletedObjects($this->limitDate),
            ],
        ];
    }

    public function archive(): void
    {
        $this->disableListener($this->em, TimestampableListener::class);
        $this->disableFilters();

        $this->initDatas();

        foreach ($this->supportGroupsToArchive as $supportGroup) {
            $this->archiveSupport($supportGroup);
        }

        $this->em->flush();
    }

    public function purge(): void
    {
        $this->disableListeners($this->em);
        $this->disableFilters();

        $this->deleteDocuments($this->documentRepo->findDeletedObjects($this->limitDate));

        /** @var RolePersonRepository $rolePersonRepo */
        $rolePersonRepo = $this->em->getRepository(RolePerson::class);
        /** @var SupportPersonRepository $supportPersonRepo */
        $supportPersonRepo = $this->em->getRepository(SupportPerson::class);
        /** @var PlaceGroupRepository $placeGroupRepo */
        $placeGroupRepo = $this->em->getRepository(PlaceGroup::class);
        /** @var PlacePersonRepository $placePersonRepo */
        $placePersonRepo = $this->em->getRepository(PlacePerson::class);
        /** @var PlaceRepository $placeRepo */
        $placeRepo = $this->em->getRepository(Place::class);
        /** @var EvaluationGroupRepository $evaluationGroupRepo */
        $evaluationGroupRepo = $this->em->getRepository(EvaluationGroup::class);
        /** @var EvaluationPersonRepository $evaluationPersonRepo */
        $evaluationPersonRepo = $this->em->getRepository(EvaluationPerson::class);
        /** @var ReferentRepository $referentRepo */
        $referentRepo = $this->em->getRepository(Referent::class);
        /** @var UserConnectionRepository $userConnectionRepo */
        $userConnectionRepo = $this->em->getRepository(UserConnection::class);

        $this->removeCollections([
            $this->peopleGroupRepo->findDeletedObjects($this->limitDate),
            $this->personRepo->findDeletedObjects($this->limitDate),
            $rolePersonRepo->findDeletedObjects($this->limitDate),
            $this->supportGroupRepo->findDeletedObjects($this->limitDate),
            $supportPersonRepo->findDeletedObjects($this->limitDate),
            $placeGroupRepo->findDeletedObjects($this->limitDate),
            $placePersonRepo->findDeletedObjects($this->limitDate),
            $placeRepo->findDeletedObjects($this->limitDate),
            $evaluationGroupRepo->findDeletedObjects($this->limitDate),
            $evaluationPersonRepo->findDeletedObjects($this->limitDate),
            $this->noteRepo->findDeletedObjects($this->limitDate),
            $this->paymentRepo->findDeletedObjects($this->limitDate),
            $this->rdvRepo->findDeletedObjects($this->limitDate),
            $this->taskRepo->findDeletedObjects($this->limitDate),
            $referentRepo->findDeletedObjects($this->limitDate),
            $userConnectionRepo->findConnectionsBeforeDate($this->limitDate),
            $this->peopleGroupRepo->findPeopleGroupWithoutSupportGroup($this->limitDate),
            $this->personRepo->findPeopleWithoutSupport($this->limitDate),
        ]);

        $this->em->flush();
    }

    /**
     * @param string $actionType "archive" or "purge"
     */
    public function getSupportGroupIds(string $actionType): array
    {
        return $this->getStats()['support_groups'][$actionType.'_ids'];
    }

    private function initDatas(): void
    {
        foreach ($this->supportGroupRepo->findEndedSupports($this->defaultArchiveDate) as $supportGroup) {
            if ($this->isArchivableSupport($supportGroup)) {
                $peopleGroup = $supportGroup->getPeopleGroup();
                $countElements = $this->getCountsBySupportGroup($supportGroup);

                $this->nbDocumentsToArchive += $countElements['nb_documents'];
                $this->nbNotesToArchive += $countElements['nb_notes'];
                $this->nbPaymentsToArchive += $countElements['nb_payments'];
                $this->nbRdvsToArchive += $countElements['nb_rdvs'];
                $this->nbTasksToArchive += $countElements['nb_tasks'];
                $this->peopleGroupIdsToArchive[] = $peopleGroup->getId();
                $this->supportGroupsToArchive[$supportGroup->getId()] = $supportGroup;
            }
        }
    }

    private function archiveSupport(SupportGroup $supportGroup): void
    {
        $this->enableFilter($this->em, 'softdeleteable');

        $peopleGroup = $supportGroup->getPeopleGroup();
        $supportGroup->setArchivedAt($this->now);
        $peopleGroup->setArchivedAt($this->now);

        foreach ($peopleGroup->getPeople() as $person) {
            $person->setArchivedAt($this->now);
        }

        // $this->supportAnonymizer->anonymize($supportGroup);

        $this->removeCollections([
            $supportGroup->getDocuments(),
            $supportGroup->getNotes(),
            $supportGroup->getPayments(),
            $supportGroup->getRdvs(),
            $supportGroup->getTasks(),
        ]);
    }

    private function removeCollections(array $collections): void
    {
        foreach ($collections as $collection) {
            foreach ($collection as $object) {
                $this->em->remove($object);
            }
        }
    }

    /**
     * @param Document[]|Collection<Document> $documents
     */
    private function deleteDocuments($documents): void
    {
        foreach ($documents as $document) {
            $filename = $this->documentsDirectory.$document->getFilePath();

            if (file_exists($filename)) {
                unlink($filename);
            }

            $this->em->remove($document);
        }
    }

    private function isArchivableSupport(SupportGroup $supportGroup): bool
    {
        foreach ($supportGroup->getPeopleGroup()->getPeople() as $person) {
            foreach ($person->getSupports() as $supportPerson) {
                if (false === $this->isValidDates($supportPerson->getSupportGroup())) {
                    return false;
                }
            }
        }

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            foreach ($supportPerson->getPerson()->getSupports() as $supportPerson) {
                if (false === $this->isValidDates($supportPerson->getSupportGroup())) {
                    return false;
                }
            }
            $this->peopleIdsToArchive[] = $supportPerson->getPerson()->getId();
        }

        return true;
    }

    private function isValidDates(SupportGroup $supportGroup): bool
    {
        return $supportGroup->getUpdatedAt() < $this->getDelayArchiveAt($supportGroup);
    }

    private function getDelayArchiveAt(SupportGroup $supportGroup): \DateTimeInterface
    {
        if (null === $this->servicesDelayArchiveDates) {
            $this->servicesDelayArchiveDates = [];
            foreach ($this->serviceRepo->findServicesWithSetting() as $service) {
                $this->servicesDelayArchiveDates[(int) $service->getId()] = (null !== $service->getSetting()
                    ? (new \DateTime())->modify('-'.$service->getSetting()->getSoftDeletionDelay().' months')
                    : $this->defaultArchiveDate
                );
            }
        }

        return $this->servicesDelayArchiveDates[$supportGroup->getService()->getId()];
    }

    private function getCountsBySupportGroup(SupportGroup $supportGroup): array
    {
        foreach ($this->getCountSupportGroupElements() as $countSupportGroupElement) {
            if ($countSupportGroupElement['id'] === $supportGroup->getId()) {
                return $countSupportGroupElement;
            }
        }

        return [
            'nb_documents' => 0,
            'nb_notes' => 0,
            'nb_payments' => 0,
            'nb_rdvs' => 0,
            'nb_tasks' => 0,
        ];
    }

    private function getCountSupportGroupElements(): array
    {
        if (null === $this->countSupportGroupElements) {
            $this->countSupportGroupElements = $this->supportGroupRepo->countSupportGroupElements($this->defaultArchiveDate);
        }

        return $this->countSupportGroupElements;
    }

    private function disableFilters(): void
    {
        $this->disableFilter($this->em, 'softdeleteable');
        $this->disableFilter($this->em, 'archive_filter');
    }
}
