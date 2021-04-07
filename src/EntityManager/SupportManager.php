<?php

namespace App\EntityManager;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Organization\Service;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Model\Support\SupportSearch;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\SupportPersonExport;
use App\Service\Grammar;
use App\Service\hydrateObjectWithArray;
use App\Service\SupportGroup\AvdlService;
use App\Service\SupportGroup\HotelSupportService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SupportManager
{
    use hydrateObjectWithArray;

    private $manager;
    private $repoSupportGroup;
    private $flashbag;
    private $cache;

    public function __construct(
        EntityManagerInterface $manager,
        SupportGroupRepository $repoSupportGroup,
        FlashBagInterface $flashbag)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->flashbag = $flashbag;
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Donne un nouveau suivi paramétré.
     */
    public function getNewSupportGroup(PeopleGroup $peopleGroup, Request $request, ServiceRepository $repoService)
    {
        $supportGroup = (new SupportGroup())->setPeopleGroup($peopleGroup);

        $serviceId = $request->request->get('support')['service'];

        if ((int) $serviceId) {
            $supportGroup->setService($repoService->find($serviceId));
        }

        return $supportGroup;
    }

    /**
     * Créé un nouveau suivi.
     */
    public function create(PeopleGroup $peopleGroup, SupportGroup $supportGroup): bool
    {
        if ($this->activeSupportExists($peopleGroup, $supportGroup)) {
            return false;
        }

        $supportGroup->setPeopleGroup($peopleGroup)
        ->setCoefficient($supportGroup->getDevice()->getCoefficient());

        $serviceType = $supportGroup->getService()->getType();

        // Contrôle le service du suivi
        if (Service::SERVICE_TYPE_AVDL === $serviceType) {
            $supportGroup = (new AvdlService())->updateSupportGroup($supportGroup);
        }
        if (Service::SERVICE_TYPE_HOTEL === $serviceType) {
            $supportGroup = (new HotelSupportService())->updateSupportGroup($supportGroup);
        }

        $this->manager->persist($supportGroup);

        // Créé un suivi social individuel pour chaque personne du groupe
        foreach ($peopleGroup->getRolePeople() as $rolePerson) {
            $supportGroup->addSupportPerson($this->createSupportPerson($supportGroup, $rolePerson));
        }

        return true;
    }

    /**
     * Crée un suivi individuel.
     */
    protected function createSupportPerson(SupportGroup $supportGroup, RolePerson $rolePerson): SupportPerson
    {
        $supportPerson = (new SupportPerson())
            ->setSupportGroup($supportGroup)
            ->setPerson($rolePerson->getPerson())
            ->setHead($rolePerson->getHead())
            ->setRole($rolePerson->getRole())
            ->setStatus($supportGroup->getStatus())
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate())
            ->setEndStatus($supportGroup->getEndStatus())
            ->setEndStatusComment($supportGroup->getEndStatusComment());

        $birthdate = $rolePerson->getPerson()->getBirthdate();

        if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $birthdate) {
            $supportPerson->setStartDate($birthdate);
            $this->addFlash('warning', $supportPerson->getPerson()->getFullname().' : la date de début de suivi retenue est sa date de naissance.');
        }

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }

    /**
     * Exporte les données.
     */
    public function exportData(SupportSearch $search, SupportPersonRepository $repoSupportPerson)
    {
        set_time_limit(10 * 60);

        $supports = $repoSupportPerson->findSupportsToExport($search);

        if (!$supports) {
            return $this->addFlash('warning', 'Aucun résultat à exporter.');
        }

        return (new SupportPersonExport())->exportData($supports);
    }

    /**
     * Donne le suivi social complet.
     */
    public function getFullSupportGroup(int $id): ?SupportGroup
    {
        $supportGroup = $this->cache->get(SupportGroup::CACHE_FULLSUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

            return $this->repoSupportGroup->findFullSupportById($id);
        });

        return $supportGroup;
    }

    /**
     * Donne le suivi social.
     */
    public function getSupportGroup(int $id): ?SupportGroup
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->repoSupportGroup->findSupportById($id);
        });
    }

    /**
     * Initialise l'évaluation sociale.
     */
    protected function initEvaluation(EvaluationGroup $evaluationGroup, NormalizerInterface $normalizer): void
    {
        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();
        $evalHousingGroup = $evaluationGroup->getEvalHousingGroup();

        $initEvalGroup = $evaluationGroup->getInitEvalGroup();

        if ($evalBudgetGroup) {
            $initEvalGroup
                    ->setResourcesGroupAmt($evalBudgetGroup->getResourcesGroupAmt())
                    ->setDebtsGroupAmt($evalBudgetGroup->getDebtsGroupAmt());
        }
        if ($evalHousingGroup) {
            $initEvalGroup
                    ->setHousingStatus($evalHousingGroup->getHousingStatus())
                    ->setSiaoRequest($evalHousingGroup->getSiaoRequest())
                    ->setSocialHousingRequest($evalHousingGroup->getSocialHousingRequest());
        }

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            /** @var EvaluationPerson */
            $evaluationPerson = $evaluationPerson;
            $evalAdminPerson = $evaluationPerson->getEvalAdmPerson();
            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
            $evalProfPerson = $evaluationPerson->getEvalProfPerson();
            $evalSocialPerson = $evaluationPerson->getEvalSocialPerson();

            $initEvalPerson = $evaluationPerson->getInitEvalPerson();

            $initEvalPerson->setPaperType($evalAdminPerson ? $evalAdminPerson->getPaperType() : null);

            if ($evalSocialPerson) {
                $arrayEvalSocialPerson = $normalizer->normalize($evalSocialPerson, null, [
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'evaluationPerson'],
                ]);
                $this->hydrateObjectWithArray($initEvalPerson, $arrayEvalSocialPerson);
            }
            if ($evalProfPerson) {
                $initEvalPerson
                    ->setProfStatus($evalProfPerson->getProfStatus())
                    ->setContractType($evalProfPerson->getContractType());
            }
            if ($evalBudgetPerson) {
                $arrayEvalBudgetPerson = $normalizer->normalize($evalBudgetPerson, null, [
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'evaluationPerson'],
                ]);
                $this->hydrateObjectWithArray($initEvalPerson, $arrayEvalBudgetPerson);
            }
        }
    }

    /**
     * Ajoute les personnes au suivi.
     */
    public function addPeopleInSupport(SupportGroup $supportGroup, EvaluationGroupRepository $repoEvaluation): bool
    {
        $addPeople = false;

        foreach ($supportGroup->getPeopleGroup()->getRolePeople() as $rolePerson) {
            if (!$this->personIsInSupport($rolePerson->getPerson(), $supportGroup)) {
                $supportPerson = $this->createSupportPerson($supportGroup, $rolePerson);

                $supportGroup->addSupportPerson($supportPerson);
                $evaluationGroup = $repoEvaluation->findLastEvaluationOfSupport($supportGroup);

                if ($evaluationGroup) {
                    $evaluationPerson = (new EvaluationPerson())
                        ->setEvaluationGroup($evaluationGroup)
                        ->setSupportPerson($supportPerson);

                    $this->manager->persist($evaluationPerson);
                }

                $this->addFlash('success', $rolePerson->getPerson()->getFullname().' est ajouté'.Grammar::gender($supportPerson->getPerson()->getGender()).' au suivi.');

                $addPeople = true;
            }
        }

        return $addPeople;
    }

    /**
     * Vérifie si un suivi social est déjà en cours dans le même service.
     */
    protected function activeSupportExists(PeopleGroup $peopleGroup, SupportGroup $supportGroup): ?SupportGroup
    {
        return $this->repoSupportGroup->findOneBy([
            'peopleGroup' => $peopleGroup,
            'status' => SupportGroup::STATUS_IN_PROGRESS,
            'service' => $supportGroup->getService(),
        ]);
    }

    /**
     * Vérifie si la personne est déjà dans le suivi social.
     */
    protected function personIsInSupport(Person $person, SupportGroup $supportGroup): bool
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($person === $supportPerson->getPerson()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Ajoute un message flash.
     */
    protected function addFlash(string $alert, string $msg)
    {
        $this->flashbag->add($alert, $msg);
    }
}
