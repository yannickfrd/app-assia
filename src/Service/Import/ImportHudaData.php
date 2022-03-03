<?php

namespace App\Service\Import;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalBudgetResource;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalInitGroup;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\EvalInitResource;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Organization\Device;
use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use App\Notification\ImportNotification;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\People\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportHudaData extends ImportDatas
{
    public const GENDERS = [
        'Femme' => Person::GENDER_FEMALE,
        'Homme' => Person::GENDER_MALE,
        '' => Choices::NO_INFORMATION,
    ];

    public const YES_NO = [
        'Oui' => 1,
        'OUI' => 1,
        'Non' => 2,
        'NON' => 2,
        'En cours' => 3,
        'NC' => 3,
        'Non concerné' => 98,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const YES_NO_BOOLEAN = [
        'Non' => 0,
        'Oui' => 1,
    ];

    public const PAPER = [
        'DA - Procédure accélérée' => 1,
        'DA - Procédure normale' => 1,
        'Débouté du droit d\'asile' => 2,
        'Procédure Dublin' => 1,
        'Procédure Schengen' => 1,
        'Protection subsidiaire (1 an)' => 1,
        'Recours CNDA' => 1,
        'Récépissé asile' => 1,
        'Réfugié statutaire (10 ans)' => 1,
        'Autre' => Choices::NO_INFORMATION,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const ASYLUM_STATUS = [
        'DA - Procédure accélérée' => 6,
        'DA - Procédure normale' => 2,
        'Débouté du droit d\'asile' => 1,
        'Procédure Dublin' => 7,
        'Procédure Schengen' => 8,
        'Protection subsidiaire (1 an)' => 3,
        'Recours CNDA' => 9,
        'Récépissé asile' => 5,
        'Réfugié statutaire (10 ans)' => 4,
        'Autre' => Choices::NO_INFORMATION,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const RESOURCES = [
        'Oui' => 1,
        'Non' => 2,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const PROF_STATUS = [
        'Sans emploi' => 2,
        'CDD temps complet' => 8,
        'CDD temps partiel' => 8,
        'CDI temps complet' => 8,
        'CDI temps partiel' => 8,
        'Intérim' => 8,
        'Apprentissage' => 3,
        'Formation' => 3,
        'Indépendant' => 6,
        'Auto-entrepreneur' => 1,
        'Autre' => 97,
        'Non concerné' => null,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const CONTRACT_TYPE = [
        'Sans emploi' => null,
        'CDD temps complet' => 1,
        'CDD temps partiel' => 1,
        'CDI temps complet' => 2,
        'CDI temps partiel' => 2,
        'Intérim' => 7,
        'Apprentissage' => 4,
        'Formation' => 5,
        'Indépendant' => 97,
        'Auto-entrepreneur' => 97,
        'Autre' => 97,
        'Non concerné' => null,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const RESOURCES_TYPE = [
        'Salaire' => 10,
        'Prime d\'activité' => 50,
        'ARE' => 30,
        'RSA' => 60,
        'AAH' => 80,
        'ASF' => 101,
        'ASS' => 90,
        'ADA' => 130,
        'Bourse' => 180,
        'Formation' => 40,
        'Garantie jeunes' => 120,
        'Retraite' => 20,
        'Autre' => 1000,
    ];

    public const RIGHT_SOCIAL_SECURITY = [
        'Sans' => 2,
        'CMU' => 1,
        'CMU-C' => 1,
        'AME' => 1,
        'Régime Général' => 1,
        'ACS' => 1,
        'En cours' => 3,
        'Autre' => 1,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const SOCIAL_SECURITY = [
        'Sans' => null, // x
        'CMU' => 3,
        'CMU-C' => 4,
        'AME' => 5,
        'Régime Général' => 1,
        'ACS' => 6,
        'En cours' => null, // x
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const END_REASON = [
        'Accès au logement privé' => 100,
        'Accès au logement/résidence sociale' => 100,
        'Départ volontaire' => 310,
        'Fin de prise charge OFII' => 400,
        'Fin de prise en charge ESPERER 95' => 200,
        'Retour volontaire dans pays d’origine' => 310,
        'SIAO/115' => 97,
        'Transfert Dublin' => 410,
        'Autre' => 97,
        'NR' => 99,
    ];

    public const END_STATUS = [
        'Accès au logement privé' => 300,
        'Accès au logement/résidence sociale' => 204,
        'Départ volontaire' => 97,
        'Fin de prise charge OFII' => 97,
        'Fin de prise en charge ESPERER 95' => 97,
        'Retour volontaire dans pays d’origine' => 97,
        'SIAO/115' => 102,
        'Transfert Dublin' => 97,
        'Autre' => 97,
        'NR' => 99,
    ];

    public const PLACE_TYPE = [
        'Chambre individuelle' => 1,
        'Chambre collective' => 2,
        "Chambre d'hôtel" => 3,
        'Dortoir' => 4,
        'Logement T1' => 5,
        'Logement T2' => 6,
        'Logement T3' => 7,
        'Logement T4' => 8,
        'Logement T5' => 9,
        'Logement T6' => 10,
        'Logement T7' => 11,
        'Logement T8' => 12,
        'Logement T9' => 13,
        'Pavillon' => 14,
        'Autre' => 97,
        'Non renseigné' => 99,
    ];

    protected $em;
    protected $importNotification;

    protected $subServiceRepo;
    protected $deviceRepo;
    protected $placeRepo;
    protected $personRepo;
    protected $slugger;

    protected $datas;
    protected $row;

    protected $fields;
    protected $field;

    protected $service;
    /** @var SubService */
    protected $subService;
    protected $place;

    protected $devices = [];
    protected $places = [];
    protected $subServices = [];

    protected $person;
    protected $personExists;

    protected $items = [];
    protected $people = [];
    protected $rolePeople = [];
    protected $duplicatedPeople = [];
    protected $existPeople = [];

    protected $gender;
    protected $head;
    protected $role;

    public function __construct(
        EntityManagerInterface $em,
        ImportNotification $importNotification,
        SubServiceRepository $subServiceRepo,
        DeviceRepository $deviceRepo,
        PlaceRepository $placeRepo,
        PersonRepository $personRepo,
        SluggerInterface $slugger
    ) {
        $this->em = $em;
        $this->importNotification = $importNotification;
        $this->subServiceRepo = $subServiceRepo;
        $this->deviceRepo = $deviceRepo;
        $this->repoPlace = $placeRepo;
        $this->personRepo = $personRepo;
        $this->slugger = $slugger;
    }

    /**
     * Importe les données.
     *
     * @param Collection<Service> $services
     */
    public function importInDatabase(string $fileName, ArrayCollection $services): array
    {
        $this->fields = $this->getDatas($fileName);
        $this->service = $services->first();
        $this->subServices = $this->subServiceRepo->findBy(['service' => $this->service]);
        $this->devices = $this->deviceRepo->getDevicesOfService($this->service);

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $this->device = $this->getDevice();
                $this->place = $this->getPlace($this->service);

                $this->person = $this->getPerson();
                $this->personExists = $this->personExistsInDatabase($this->person);

                $peopleGroup = $this->createPeopleGroup();
                $this->person = $this->createPerson($peopleGroup);

                $supportGroup = $this->createSupportGroup($peopleGroup);

                if ($this->place) {
                    $placeGroup = $this->createPlaceGroup($peopleGroup, $supportGroup);
                }

                $evaluationGroup = $this->createEvaluationGroup($supportGroup);

                $supportPerson = $this->createSupportPerson($supportGroup);
                if ($supportPerson->getStartDate()) {
                    if ($this->place) {
                        $this->createPlacePerson($this->person, $placeGroup, $supportPerson);
                    }
                    $this->createEvaluationPerson($evaluationGroup, $supportPerson);
                }
            }
            ++$i;
        }

        if (count($this->existPeople) > 0) {
            $this->sendDuplicatedPeople($this->existPeople);
        }
        if (count($this->duplicatedPeople) > 0) {
            $this->sendDuplicatedPeople($this->duplicatedPeople);
        }

        // dump($this->existPeople);
        // dump($this->duplicatedPeople);
        // dd($this->places);
        // dd($this->items);
        $this->em->flush();

        return $this->items;
    }

    protected function getPerson(): Person
    {
        return (new Person())
            ->setLastname($this->field['Nom'])
            ->setFirstname($this->field['Prénom'])
            ->setGender(Person::GENDER_MALE)
            ->setBirthdate($this->field['Date naissance'] ? new \Datetime($this->field['Date naissance']) : null)
        ;
    }

    protected function createPeopleGroup(): PeopleGroup
    {
        if ($person = $this->personExistsInDatabase()) {
            /** @var RolePerson $rolePerson */
            $rolePerson = $person->getRolesPerson()->first();

            if ($rolePerson) {
                return $rolePerson->getPeopleGroup();
            }
        }

        $peopleGroup = (new PeopleGroup())
            ->setFamilyTypology(2)
            ->setNbPeople(1)
        ;

        $this->em->persist($peopleGroup);

        return $peopleGroup;
    }

    protected function createSupportGroup(PeopleGroup $peopleGroup): SupportGroup
    {
        $supportGroup = (new SupportGroup())
            ->setStatus($this->getStatus())
            ->setStartDate($this->getStartDate())
            ->setEndReason($this->findInArray($this->field['Type sortie'], self::END_REASON))
            ->setEndDate($this->getEndDate())
            ->setEndStatus($this->findInArray($this->field['Type sortie'], self::END_STATUS))
            ->setEndStatusComment($this->field['Commentaire sur la sortie'])
            ->setNbPeople(1)
            ->setPeopleGroup($peopleGroup)
            ->setService($this->service)
            ->setSubService($this->subService)
            ->setDevice($this->device)
        ;

        $this->em->persist($supportGroup);

        return $supportGroup;
    }

    protected function getDevice(): Device
    {
        foreach ($this->devices as $device) {
            if ('HUDA' === $device->getName()) {
                return $device;
            }
        }

        throw new Exception('Dispositif inconnu : '.$this->field['Dispositif']);

        return null;
    }

    protected function createEvaluationGroup($supportGroup): EvaluationGroup
    {
        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setEvalInitGroup($this->createEvalInitGroup($supportGroup))
            ->setDate($supportGroup->getCreatedAt())
            ->setConclusion($this->field['Commentaire situation'])
        ;

        $this->em->persist($evaluationGroup);

        $this->createEvalBudgetGroup($evaluationGroup);
        $this->createEvalHousingGroup($evaluationGroup);

        return $evaluationGroup;
    }

    protected function createEvalInitGroup(SupportGroup $supportGroup): EvalInitGroup
    {
        $evalInitGroup = (new EvalInitGroup())
            ->setSiaoRequest($this->findInArray($this->field['Demande SIAO active'], self::YES_NO))
            ->setResourcesGroupAmt((float) $this->field['Montant ressources (entrée)'])
            ->setDebtsGroupAmt(null)
            ->setSupportGroup($supportGroup);

        $this->em->persist($evalInitGroup);

        return $evalInitGroup;
    }

    protected function createEvalBudgetGroup(EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setResourcesGroupAmt((float) $this->field['Montant ressources'])
            ->setBudgetBalanceAmt((float) $this->field['Montant ressources'])
            ->setEvaluationGroup($evaluationGroup)
        ;

        $this->em->persist($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(EvaluationGroup $evaluationGroup): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
            ->setSiaoRequest($this->findInArray($this->field['Demande SIAO active'], self::YES_NO))
            ->setEvaluationGroup($evaluationGroup)
        ;

        $this->em->persist($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function createPerson(PeopleGroup $peopleGroup): Person
    {
        $duplicatedPerson = false;

        if ($this->personExists) {
            $this->person = $this->personExists;
            $this->existPeople[] = $this->person;
        } else {
            foreach ($this->people as $person2) {
                if ($this->person->getLastname() === $person2->getLastname()
                    && $this->person->getFirstname() === $person2->getFirstname()
                    && $this->person->getBirthdate() === $person2->getBirthdate()) {
                    $this->duplicatedPeople[] = $this->person;
                    $duplicatedPerson = true;
                    $this->person = $person2;
                }
            }
            if (false === $duplicatedPerson) {
                $this->em->persist($this->person);
                $this->person->addRolesPerson($this->createRolePerson($peopleGroup));
                $this->people[] = $this->person;
            }
        }

        $this->items[] = $this->person;

        return $this->person;
    }

    protected function personExistsInDatabase(): ?Person
    {
        return $this->personRepo->findOneBy([
            'firstname' => $this->person->getFirstname(),
            'lastname' => $this->person->getLastname(),
            'birthdate' => $this->person->getBirthdate(),
        ]);
    }

    protected function createRolePerson(PeopleGroup $peopleGroup): RolePerson
    {
        $rolePerson = (new RolePerson())
            ->setHead(true)
            ->setRole(5)
            ->setPerson($this->person)
            ->setPeopleGroup($peopleGroup)
        ;

        $this->em->persist($rolePerson);

        return $rolePerson;
    }

    protected function createSupportPerson(SupportGroup $supportGroup): SupportPerson
    {
        $rolePerson = $this->person->getRolesPerson()->first();

        $supportPerson = (new SupportPerson())
            ->setStatus($this->getStatus())
            ->setStartDate($this->getStartDate())
            ->setEndDate($this->getEndDate())
            ->setEndStatus($this->findInArray($this->field['Type sortie'], self::END_STATUS))
            ->setEndStatusComment($this->field['Commentaire sur la sortie'])
            ->setHead($rolePerson->getHead() ?? false)
            ->setRole($rolePerson->getRole() ?? Choices::NO_INFORMATION)
            ->setSupportGroup($supportGroup)
            ->setPerson($this->person)
        ;

        $this->em->persist($supportPerson);

        return $supportPerson;
    }

    protected function createEvaluationPerson(EvaluationGroup $evaluationGroup, SupportPerson $supportPerson): EvaluationPerson
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson)
            ->setEvalInitPerson($this->createEvalInitPerson($supportPerson))
        ;

        $this->em->persist($evaluationPerson);

        $this->createEvalSocialPerson($evaluationPerson);
        $this->createEvalAdmPerson($evaluationPerson);
        $this->createEvalBudgetPerson($evaluationPerson);
        $this->createEvalProfPerson($evaluationPerson);

        return $evaluationPerson;
    }

    protected function createEvalInitPerson(SupportPerson $supportPerson): ?EvalInitPerson
    {
        $evalInitPerson = (new EvalInitPerson())
            ->setPaper($this->findInArray($this->field['Situation administrative (entrée)'], self::PAPER))
            ->setPaperType(10)
            ->setRightSocialSecurity($this->findInArray($this->field['Couverture maladie (entrée)'], self::RIGHT_SOCIAL_SECURITY))
            ->setSocialSecurity($this->findInArray($this->field['Couverture maladie (entrée)'], self::SOCIAL_SECURITY))
            ->setProfStatus($this->findInArray($this->field['Emploi (entrée)'], self::PROF_STATUS))
            ->setContractType($this->findInArray($this->field['Emploi (entrée)'], self::CONTRACT_TYPE))
            ->setResource((float) $this->field['Montant ressources (entrée)'] > 0 ? Choices::YES : Choices::NO)
            ->setResourcesAmt((float) $this->field['Montant ressources (entrée)'])
            ->setDebt(Choices::NO_INFORMATION)
            ->setComment($this->field['Commentaire situation à l\'entrée'])
            ->setSupportPerson($supportPerson);

        foreach (self::RESOURCES_TYPE as $key => $value) {
            if ($this->field['Ressources (entrée)'] === $key) {
                $evalInitResource = (new EvalInitResource())
                    ->setAmount((float) $this->field['Montant ressources (entrée)'])
                    ->setType($value);

                $this->em->persist($evalInitResource);

                $evalInitPerson->addEvalBudgetResource($evalInitResource);
            }
        }

        $this->em->persist($evalInitPerson);

        return $evalInitPerson;
    }

    protected function createEvalSocialPerson(EvaluationPerson $evaluationPerson): EvalSocialPerson
    {
        $evalSocialPerson = (new EvalSocialPerson())
            ->setRightSocialSecurity($this->findInArray($this->field['Couverture maladie'], self::RIGHT_SOCIAL_SECURITY))
            ->setSocialSecurity($this->findInArray($this->field['Couverture maladie'], self::SOCIAL_SECURITY))
            ->setEvaluationPerson($evaluationPerson)
        ;

        $this->em->persist($evalSocialPerson);

        return $evalSocialPerson;
    }

    protected function createEvalAdmPerson(EvaluationPerson $evaluationPerson): ?EvalAdmPerson
    {
        $comment = '';

        if ($this->field['Orientation vers les soins/santé']) {
            $comment = $comment.'Orientation vers les soins/santé : '.$this->field['Orientation vers les soins/santé']."\n";
        }

        $evalAdmPerson = (new EvalAdmPerson())
            ->setCommentEvalAdmPerson($comment)
            ->setEvaluationPerson($evaluationPerson)
            ->setNationality(EvalAdmPerson::NATIONALITY_OUTSIDE_EU)
            ->setCountry($this->field['Nationalité'])
            ->setPaper($this->findInArray($this->field['Situation administrative'], self::PAPER))
            ->setPaperType(10)
            ->setAsylumBackground(Choices::YES)
            ->setAsylumStatus($this->findInArray($this->field['Situation administrative'], self::ASYLUM_STATUS))
            ->setAgdrefId($this->field['Numéro AGDREF'])
            ->setOfpraRegistrationId($this->field['Numéro OFPRA'])
        ;

        $this->em->persist($evalAdmPerson);

        return $evalAdmPerson;
    }

    protected function createEvalProfPerson(EvaluationPerson $evaluationPerson): ?EvalProfPerson
    {
        $evalProfPerson = (new EvalProfPerson())
            ->setCommentEvalProf($this->field['Orientation vers l\'emploi'] ? 'Orientation vers l\'emploi : '.$this->field['Orientation vers l\'emploi'] : null)
            ->setProfStatus($this->findInArray($this->field['Emploi'], self::PROF_STATUS))
            ->setContractType($this->findInArray($this->field['Emploi'], self::CONTRACT_TYPE))
            ->setEvaluationPerson($evaluationPerson)
        ;

        $this->em->persist($evalProfPerson);

        return $evalProfPerson;
    }

    protected function createEvalBudgetPerson(EvaluationPerson $evaluationPerson): ?EvalBudgetPerson
    {
        $evalBudgetPerson = (new EvalBudgetPerson())
            ->setResource((float) $this->field['Montant ressources'] > 0 ? Choices::YES : Choices::NO)
            ->setResourcesAmt((float) $this->field['Montant ressources'])
            ->setEvaluationPerson($evaluationPerson)
        ;

        foreach (self::RESOURCES_TYPE as $key => $value) {
            if ($this->field['Ressources'] === $key) {
                $evalBudgetResource = (new EvalBudgetResource())
                    ->setAmount((float) $this->field['Montant ressources'])
                    ->setType($value);

                $this->em->persist($evalBudgetResource);

                $evalBudgetPerson->addEvalBudgetResource($evalBudgetResource);
            }
        }

        $this->em->persist($evalBudgetPerson);

        return $evalBudgetPerson;
    }

    protected function getStatus(): int
    {
        return $this->field['Date sortie'] ? SupportGroup::STATUS_ENDED : ($this->field['Date entrée'] ? SupportGroup::STATUS_IN_PROGRESS : SupportGroup::STATUS_PRE_ADD_FAILED);
    }

    protected function getStartDate(): ?\DateTime
    {
        return $this->field['Date entrée'] ? new \Datetime($this->field['Date entrée']) : null;
    }

    protected function getEndDate(): ?\DateTime
    {
        return $this->field['Date sortie'] ? new \Datetime($this->field['Date sortie']) : null;
    }

    protected function getPlace(): ?Place
    {
        $placeExists = false;
        $placeName = strtolower($this->slugger->slug($this->field['Nom du logement']));

        if ('' === $placeName) {
            return null;
        }

        foreach ($this->places as $key => $value) {
            if ((string) $key === $placeName) {
                $placeExists = true;
            }
        }

        if (!$placeExists) {
            $this->places[$placeName] = $this->createPlace($this->device);
        }

        return $this->places[$placeName];
    }

    protected function createPlace(Device $device): Place
    {
        $place = (new Place())
            ->setComment($this->field['Adresse logement'])
            ->setConfiguration(1)
            ->setIndividualCollective(2)
            ->setName($this->field['Nom du logement'])
            ->setAddress($this->field['Adresse logement'])
            ->setNbPlaces((int) $this->field['Nombre de places'])
            ->setStartDate(isset($this->field['Date ouverture']) ? new \Datetime($this->field['Date ouverture']) : new \Datetime('2020-01-01'))
            ->setPlaceType($this->findInArray($this->field['Type logement'], self::PLACE_TYPE))
            ->setDevice($device)
            ->setService($this->service)
        ;

        $this->em->persist($place);

        return $place;
    }

    protected function createPlaceGroup(PeopleGroup $peopleGroup, SupportGroup $supportGroup): ?PlaceGroup
    {
        $placeGroup = (new PlaceGroup())
            ->setStartDate($supportGroup->getStartDate() ? $supportGroup->getStartDate() : null)
            ->setEndDate($supportGroup->getEndDate() ? $supportGroup->getEndDate() : null)
            ->setEndReason($supportGroup->getEndDate() ? Choices::YES : null)
            ->setPeopleGroup($peopleGroup)
            ->setSupportGroup($supportGroup)
            ->setPlace($this->place)
        ;

        $this->em->persist($placeGroup);

        return $placeGroup;
    }

    protected function createPlacePerson(Person $person, PlaceGroup $placeGroup, SupportPerson $supportPerson): PlacePerson
    {
        $placePerson = (new PlacePerson())
            ->setStartDate($supportPerson->getStartDate() ? $supportPerson->getStartDate() : null)
            ->setEndDate($supportPerson->getEndDate() ? $supportPerson->getEndDate() : null)
            ->setEndReason($supportPerson->getEndDate() ? Choices::YES : null)
            ->setPlaceGroup($placeGroup)
            ->setPerson($person)
            ->setSupportPerson($supportPerson)
        ;

        $this->em->persist($placePerson);

        return $placePerson;
    }
}
