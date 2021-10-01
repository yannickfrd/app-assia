<?php

namespace App\Service\Import;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalFamilyGroup;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialGroup;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\InitEvalGroup;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\Organization\Device;
use App\Entity\Organization\Place;
use App\Entity\Organization\Referent;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\OriginRequest;
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

class ImportDatasHebergement extends ImportDatas
{
    public const SOCIAL_WORKER = [
    ];

    public const FAMILY_TYPOLOGY = [
        'Femme isolée' => 1,
        'Homme isolé' => 2,
        'Couple sans enfant' => 3,
        'Femme avec enfant(s)' => 4,
        'Homme avec enfant(s)' => 5,
        'Couple avec enfant(s)' => 6,
        'Autre' => 9,
    ];

    public const ROLE = [
        'DP' => 5,
        'Conjoint·e' => 1,
        'Enfant' => 3,
        'Famille' => 6,
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const GENDERS = [
        'Femme' => Person::GENDER_FEMALE, // A Vérifier
        'Homme' => Person::GENDER_MALE, // A Vérifier
        '' => Choices::NO_INFORMATION,
    ];

    public const YES_NO = [
        'Oui' => 1,
        'Non' => 2,
        'En cours' => 3,
        'NC' => 3,
        'Non concerné' => 98,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const YES_NO_BOOLEAN = [
        'Non' => 0,
        'Oui' => 1,
    ];

    public const RESULT_PRE_ADMISSION = [
        'En cours' => 1,
        'Admission' => 2,
        'Refus structure' => 3,
        'Refus ménage' => 4,
        'Refus autre' => 5,
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const REASON_REQUEST = [
        'Absence de ressource' => 1,
        'Départ du département initial' => 2,
        'Dort dans la rue' => 3,
        'Exil économique' => 4,
        'Exil familial' => 5,
        'Exil politique' => 6,
        'Exil soins' => 7,
        'Exil autre motif' => 8,
        'Expulsion locative' => 9,
        'Fin de prise en charge ASE' => 10,
        'Fin d\'hébergement chez des tiers' => 11,
        'Fin d\'hospitalisation' => 12,
        'Fin prise en charge Conseil Départemental' => 13,
        'Grande exclusion' => 14,
        'Inadaptation du logement' => 15,
        'Logement insalubre' => 16,
        'Logement repris par le propriétaire' => 17,
        'Rapprochement du lieu de travail' => 18,
        'Regroupement familial' => 19,
        'Risque d\'expulsion locative' => 20,
        'Séparation ou rupture des liens familiaux' => 21,
        'Sortie de détention' => 22,
        'Sortie de logement accompagné' => 23,
        'Sortie d\'hébergement' => 24,
        'Sortie dispositif asile' => 25,
        'Traite humaine' => 26,
        'Violences familiales-conjugales' => 27,
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const CARE_SUPPORT = [
        'Non' => 2,
        'Infirmier à domicile' => 1,
        'PCH' => 1,
        'SAMSAH' => 1,
        'SAVS' => 1,
        'Autre' => 1,
        'Non renseignée' => Choices::NO_INFORMATION,
    ];

    public const CARE_SUPPORT_TYPE = [
        'Infirmier à domicile' => 1,
        'PCH' => 2,
        'SAMSAH' => 3,
        'SAVS' => 4,
        'Autre' => 97,
        'Non renseignée' => Choices::NO_INFORMATION,
    ];

    public const HOUSING_STATUS = [
        'A la rue - abri de fortune' => 001,
        'CADA' => 400,
        'Colocation' => 304,
        'Détention' => 500,
        'Dispositif hivernal' => 105,
        'Dispositif médical (LHSS, LAM, autre)' => 602,
        'Errance résidentielle' => 003,
        'DLSAP' => 502,
        'Hébergé chez des tiers' => 010,
        'Hébergé chez famille' => 011,
        'Hôtel 115' => 100,
        'Hôtel (hors 115)' => 101,
        'Hébergement d’urgence' => 102,
        'Hébergement de stabilisation' => 103,
        'Hébergement d’insertion' => 104,
        'Hôpital' => 600,
        'HUDA' => 401,
        'LHSS' => 601,
        'Logement adapté - ALT' => 200,
        'Logement adapté - FJT' => 201,
        'Logement adapté - FTM' => 202,
        'Maison relais' => 203,
        'Résidence sociale' => 204,
        'Logement adapté - RHVS' => 205,
        'Logement adapté - Solibail/IML' => 206,
        'Logement foyer' => 207,
        'Location parc privé' => 300,
        'Location parc public' => 301,
        'Placement extérieur' => 501,
        "Propriétaire d'un logement" => 303,
        'Sous-location' => 302,
        'Squat' => 002,
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const PLACE_HOUSING_STATUS = [
        'HU' => 102,
        'Stabilisation' => 103,
        'Insertion' => 104,
        'Maison relais' => 203,
    ];

    public const NATIONALITY = [
        'France' => 1,
        'UE' => 2,
        'Hors-UE' => 3,
        'Apatride' => 4,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const PAPER = [
        'Autorisation provisoire de séjour' => 1,
        'Carte de résident (10 ans)' => 1,
        'Carte de séjour temporaire' => 1,
        'Carte d\'identité européenne' => 1,
        'CNI française' => 1,
        'DCEM' => 1,
        'Demandeur d\'asile' => 1,
        'Démarche en cours' => 3,
        'OQTF' => 2,
        'Récépissé asile' => 1,
        'Récépissé de 1ère demande' => 1,
        'Récépissé renouvellement de titre' => 1,
        'Réfugié' => 1,
        'Sans titre de séjour' => 2,
        'Titre de séjour "vie privée et familiale"' => 1,
        'Titre de séjour pour soins' => 1,
        'Titre d\'Identité Républicain (TIR)' => 1,
        'Visa de court séjour' => 1,
        'Visa de long séjour' => 1,
        'Autre' => Choices::NO_INFORMATION,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const PAPER_TYPE = [
        'Autorisation provisoire de séjour' => 22,
        'Carte de résident (10 ans)' => 20,
        'Carte de séjour temporaire' => 21,
        'Carte d\'identité européenne' => 01,
        'CNI française' => 01,
        'DCEM' => 40,
        'Demandeur d\'asile' => null,
        'Démarche en cours' => null,
        'OQTF' => null,
        'Récépissé asile' => 30,
        'Récépissé de 1ère demande' => 30,
        'Récépissé renouvellement de titre' => 31,
        'Réfugié' => 20,
        'Sans titre de séjour' => null,
        'Titre de séjour "vie privée et familiale"' => 21,
        'Titre de séjour pour soins' => 21,
        'Titre d\'Identité Républicain (TIR)' => 40,
        'Visa de court séjour' => 97,
        'Visa de long séjour' => 97,
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const ASYLUM_BACKGROUND = [
        'Autorisation provisoire de séjour' => 2,
        'Carte de résident (10 ans)' => null,
        'Carte de séjour temporaire' => 2,
        'Carte d\'identité européenne' => null,
        'CNI française' => null,
        'DCEM' => null,
        'Demandeur d\'asile' => 1,
        'Démarche en cours' => null,
        'OQTF' => null,
        'Récépissé asile' => 1,
        'Récépissé de 1ère demande' => 2,
        'Récépissé renouvellement de titre' => 2,
        'Réfugié' => 1,
        'Sans titre de séjour' => null,
        'Titre de séjour "vie privée et familiale"' => 2,
        'Titre de séjour pour soins' => null,
        'Titre d\'Identité Républicain (TIR)' => 1,
        'Visa de court séjour' => 2,
        'Visa de long séjour' => 2,
        'Autre' => null,
        'NR' => null,
    ];

    public const CHILDCARE_SCHOOL = [
        'Assistante maternelle' => 4,
        'Crèche' => 1,
        'Ecole' => 2,
        'Parent' => 3,
        'Nourrice' => 4,
        'Autre' => 97,
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

    public const PROTECTIVE_MEASURE = [
        'Non' => 2,
        'MASP' => 1,
        'Sauvegarde de justice' => 1,
        'Curatelle simple' => 1,
        'Curatelle renforcée' => 1,
        'Tutelle' => 1,
        'Autre' => 1,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const PROTECTIVE_MEASURE_TYPE = [
        'Non' => null,
        'MASP' => 7,
        'Sauvegarde de justice' => 4,
        'Curatelle simple' => 2,
        'Curatelle renforcée' => 3,
        'Tutelle' => 1,
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const END_STATUS = [
        'Retour à la rue, squat' => 001, // A la rue - abri de fortune
        'Accès à la propriété' => 303,
        'ALTHO' => 208,
        'CADA' => 400,
        'Logement partagé / Colocation' => 304, // Colocation
        'Décès' => 900,
        'Départ volontaire de la personne' => 700,
        'Incarcération' => 500, // Détention
        'Dispositif hivernal' => 105,
        'Sortie vers le soin' => 602, // Dispositif de soin ou médical (LAM, autre)
        'DLSAP' => 502,
        'Exclusion de la structure' => 701,
        'Fin du contrat de séjour' => 702,
        'Foyer maternel' => 106,
        'Hébergement chez des tiers' => 010, // Hébergé chez des tiers
        'Hébergé chez famille' => 011,
        'Hôtel 115' => 100,
        'Hôtel au mois' => 101, // Hôtel (hors 115)
        'CHU' => 102, // Hébergement d’urgence
        'Centre de stabilisation' => 103, // Hébergement de stabilisation
        'CHRS' => 104, // Hébergement d’insertion
        'Hôpital' => 600,
        'HUDA' => 401,
        'LHSS' => 601,
        'ALT' => 200,
        'FJT' => 201, // Logement adapté - FJT
        'Logement adapté - FTM' => 202,
        'Logement adapté - Maison relais' => 203,
        'Logement adapté - RHVS' => 205,
        'Résidence sociale' => 204, // Logement adapté - Résidence sociale
        'Logement adapté - RHVS' => 205,
        'Solibail' => 206, // Logement adapté - Solibail/IML
        'Logement foyer' => 207,
        'Logement privé' => 300,
        'Logement social' => 301,
        'Maison de retraite' => 305,
        'Placement extérieur' => 501,
        "Retour dans le pays d'origine" => 704,
        'Sous-location' => 302,
        'Squat' => 002,
        'Autre' => 97,
        'NR' => Choices::NO_INFORMATION,
    ];

    public const PLACE_TYPE = [
        'Chambre individuelle' => 1,
        'Chambre collective' => 2,
        "Chambre d'hôtel" => 3,
        'Dortoir' => 4,
        'Logement F1' => 5,
        'Logement F2' => 6,
        'Logement F3' => 7,
        'Logement F4' => 8,
        'Logement F5' => 9,
        'Logement F6' => 10,
        'Logement F7' => 11,
        'Logement F8' => 12,
        'Logement F9' => 13,
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

    public const CONFIGURATION = [
        'Diffus' => 1,
        'Regroupé' => 2,
    ];

    public const INDIVIDUAL_COLLECTIVE = [
        'Individuel' => 1,
        'Collectif' => 2,
        'Autre' => 97,
    ];

    protected $manager;
    protected $importNotification;

    protected $subServiceRepo;
    protected $deviceRepo;
    protected $placeRepo;
    protected $personRepo;

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
        EntityManagerInterface $manager,
        ImportNotification $importNotification,
        SubServiceRepository $subServiceRepo,
        DeviceRepository $deviceRepo,
        PlaceRepository $placeRepo,
        PersonRepository $personRepo)
    {
        $this->manager = $manager;
        $this->importNotification = $importNotification;
        $this->subServiceRepo = $subServiceRepo;
        $this->deviceRepo = $deviceRepo;
        $this->repoPlace = $placeRepo;
        $this->personRepo = $personRepo;
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
        // $this->places = $this->repoPlace->findBy(['service' => $service]);

        // $this->users = $this->getUsers();

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $typology = $this->findInArray($this->field['Typologie familiale'], self::FAMILY_TYPOLOGY) ?? 9;
                $this->device = $this->getDevice();
                $this->place = $this->getPlace($this->service);

                $this->getRoleAndGender($typology);
                $this->person = $this->getPerson();
                $this->personExists = $this->personExistsInDatabase($this->person);

                $this->checkGroupExists($typology);

                $this->person = $this->createPerson($this->items[$this->field['N° ménage']]['peopleGroup']);

                $support = $this->items[$this->field['N° ménage']]['supports'][$this->field['N° ménage']];
                $supportGroup = $support['support'];
                $placeGroup = $support['placeGroup'];
                $evaluationGroup = $support['evaluation'];

                $supportPerson = $this->createSupportPerson($supportGroup);
                if ($supportPerson->getStartDate()) {
                    $this->createPlacePerson($this->person, $placeGroup, $supportPerson);
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
        // dd($this->items);
        $this->manager->flush();

        return $this->items;
    }

    protected function getPerson()
    {
        $person = (new Person())
                ->setLastname($this->field['Nom ménage'])
                ->setFirstname($this->field['Prénom'])
                ->setBirthdate($this->field['Date naissance'] ? new \Datetime($this->field['Date naissance']) : null)
                ->setGender($this->gender)
                ->setCreatedBy($this->user)
                ->setUpdatedBy($this->user);

        return $person;
    }

    protected function checkGroupExists(int $typology)
    {
        // Si le groupe n'existe pas encore, on le crée ainsi que le suivi et l'évaluation sociale.
        if (false === $this->groupExists()) {
            // Si la personne existe déjà dans la base de données, on récupère son groupe.
            if ($this->personExists) {
                $peopleGroup = $this->personExists->getRolesPerson()->first()->getPeopleGroup();
            // Sinon, on crée le groupe.
            } else {
                $peopleGroup = $this->createPeopleGroup($typology);
            }

            $supportGroup = $this->createSupportGroup($peopleGroup);
            if ($supportGroup->getStartDate()) {
                $placeGroup = $this->createPlaceGroup($peopleGroup, $supportGroup);
                $this->createReferent($peopleGroup);
                $evaluationGroup = $this->createEvaluationGroup($supportGroup);
            }

            // On ajoute le groupe et le suivi dans le tableau associatif.
            $this->items[(int) $this->field['N° ménage']] = [
                'peopleGroup' => $peopleGroup,
                'supports' => [
                    (int) $this->field['N° ménage'] => [
                        'support' => $supportGroup,
                        'placeGroup' => $placeGroup ?? null,
                        'evaluation' => $evaluationGroup ?? null,
                    ],
                ],
            ];
        }
    }

    protected function groupExists()
    {
        $groupExists = false;
        // Vérifie si le groupe de la personne existe déjà.
        foreach ($this->items as $key => $value) {
            // Si déjà créé, on vérifie le suivi social.
            if ($key === (int) $this->field['N° ménage']) {
                $groupExists = true;

                $supports = $this->items[$this->field['N° ménage']]['supports'];

                $supportExists = false;
                // Vérifie si le suivi du groupe de la personne a déjà été créé.
                foreach ($supports as $key => $value) {
                    if ($key === (int) $this->field['N° ménage']) {
                        $supportExists = true;
                    }
                }

                // Si le suivi social du groupe n'existe pas encore, on le crée ainsi que l'évaluation sociale.
                if (false === $supportExists) {
                    $supportGroup = $this->createSupportGroup($this->items[$this->field['N° ménage']]['peopleGroup']);
                    $evaluationGroup = $this->createEvaluationGroup($supportGroup);

                    $this->items[$this->field['N° ménage']]['supports'][$this->field['N° ménage']] = [
                        'support' => $supportGroup,
                        'evaluation' => $evaluationGroup,
                    ];
                }
            }
        }

        return $groupExists;
    }

    protected function createPeopleGroup(int $typology): PeopleGroup
    {
        if (' DP' === $this->field['Rôle']) {
            $this->personExistsInDatabase();
        }

        $peopleGroup = (new PeopleGroup())
                    ->setFamilyTypology($typology)
                    ->setNbPeople((int) $this->field['Nb personnes'])
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($peopleGroup);

        return $peopleGroup;
    }

    protected function createSupportGroup(PeopleGroup $peopleGroup): SupportGroup
    {
        $this->subService = $this->getSubService();

        // $userReferent = $this->getUserReferent();
        $supportGroup = new SupportGroup();
        $supportGroup->setStatus($this->getStatus())
                    ->setStartDate($this->getStartDate())
                    ->setEndDate($this->getEndDate())
                    ->setEndStatus($this->findInArray($this->field['Type sortie'], self::END_STATUS))
                    ->setEndStatusComment($this->field['Commentaire sur la sortie'])
                    ->setNbPeople((int) $this->field['Nb personnes'])
                    ->setPeopleGroup($peopleGroup)
                    ->setService($this->service)
                    ->setSubService($this->subService)
                    ->setDevice($this->device)
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user)
                    ->setComment(null);

        $this->manager->persist($supportGroup);

        $this->createOriginRequest($supportGroup);

        return $supportGroup;
    }

    protected function createOriginRequest(SupportGroup $supportGroup): ?OriginRequest
    {
        if (!$this->field['Service prescripteur'] && !$this->field['Date entretien pré-admission'] && !$this->field['Date entretien pré-admission']) {
            return null;
        }

        $originRequest = new OriginRequest();
        $originRequest->setInfoToSiaoDate($this->field['Date entretien pré-admission'] ? new \Datetime($this->field['Date remise à dispo SIAO']) : null)
            ->setOrientationDate($this->field['Date entretien pré-admission'] ? new \Datetime($this->field['Date remise à dispo SIAO']) : null)
            ->setOrganizationComment($this->field['Service prescripteur'])
            ->setPreAdmissionDate($this->field['Date entretien pré-admission'] ? new \Datetime($this->field['Date entretien pré-admission']) : null)
            ->setResulPreAdmission($this->findInArray($this->field['Résultat entretien pré-admission'], self::RESULT_PRE_ADMISSION))
            ->setComment($this->field['Commentaire pré-admission'])
            ->setSupportGroup($supportGroup);

        $this->manager->persist($originRequest);

        return $originRequest;
    }

    protected function getSubService(): ?SubService
    {
        if (!isset($this->field['Sous-service']) || !$this->field['Sous-service']) {
            return null;
        }

        foreach ($this->subServices as $subService) {
            if ($this->field['Sous-service'] === $subService->getName()) {
                return $subService;
            }
        }

        return null;
    }

    protected function getDevice(): Device
    {
        foreach ($this->devices as $device) {
            if ($this->field['Dispositif'] === $device->getName()) {
                return $device;
            }
        }

        throw new Exception('Dispositif inconnu : '.$this->field['Dispositif']);

        return null;
    }

    protected function getUserReferent(): ?User
    {
        foreach ($this->users as $key => $user) {
            if ($key === $this->field['TS AMH']) {
                return $user;
            }
        }

        return null;
    }

    protected function createEvaluationGroup($supportGroup): EvaluationGroup
    {
        $conclusion = '';

        if ($this->field['Accompagnement social mis en place']) {
            $conclusion = $conclusion.'Accompagnement social mis en place : '.$this->field['Accompagnement social mis en place']."\n";
        }
        if ($this->field['Orientation vers autre association']) {
            $conclusion = $conclusion.'Orientation vers autre association : '.$this->field['Orientation vers autre association']."\n";
        }
        if ($this->field['Commentaire situation']) {
            $conclusion = $conclusion.$this->field['Commentaire situation']."\n";
        }

        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setInitEvalGroup($this->createInitEvalGroup($supportGroup))
            ->setDate($supportGroup->getCreatedAt())
            ->setConclusion($conclusion)
            ->setCreatedAt($supportGroup->getCreatedAt())
            ->setUpdatedAt($supportGroup->getUpdatedAt())
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->manager->persist($evaluationGroup);

        $this->createEvalSocialGroup($evaluationGroup);
        $this->createEvalFamilyGroup($evaluationGroup);
        $this->createEvalBudgetGroup($evaluationGroup);
        $this->createEvalHousingGroup($evaluationGroup);

        return $evaluationGroup;
    }

    protected function createInitEvalGroup(SupportGroup $supportGroup): InitEvalGroup
    {
        $initEvalGroup = (new InitEvalGroup())
            ->setHousingStatus($this->findInArray($this->field['Situation résidentielle (avant entrée)'], self::HOUSING_STATUS))
            ->setSiaoRequest($this->findInArray($this->field['Demande SIAO active'], self::YES_NO))
            ->setSocialHousingRequest($this->findInArray($this->field['Demande logement social (entrée)'], self::YES_NO))
            ->setResourcesGroupAmt((float) $this->field['Total ressources ménage (entrée)'])
            ->setDebtsGroupAmt(null)
            ->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        return $initEvalGroup;
    }

    protected function createEvalFamilyGroup(EvaluationGroup $evaluationGroup): ?EvalFamilyGroup
    {
        if (!$this->field['Enfants au pays'] && !$this->field['Commentaire situation familiale']) {
            return null;
        }

        $evalFamilyGroup = (new EvalFamilyGroup())
                ->setChildrenBehind((int) $this->field['Enfants au pays'] > 0 ? Choices::YES : Choices::NO)
                ->setCommentEvalFamilyGroup($this->field['Commentaire situation familiale'])
                ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalFamilyGroup);

        return $evalFamilyGroup;
    }

    protected function createEvalSocialGroup(EvaluationGroup $evaluationGroup): ?EvalSocialGroup
    {
        if (!$this->field['Raison principale de la demande'] && !$this->field['Durée d\'errance']) {
            return null;
        }

        $evalSocialGroup = (new EvalSocialGroup())
                ->setReasonRequest($this->findInArray($this->field['Raison principale de la demande'], self::REASON_REQUEST))
                ->setWanderingTime($this->getWanderingTime((float) $this->field['Durée d\'errance']))
                ->setCommentEvalSocialGroup($this->field['Commentaire situation résidentielle'])
                ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalSocialGroup);

        return $evalSocialGroup;
    }

    protected function getWanderingTime($time)
    {
        if ($time <= 0.25) {
            return 1;
        } elseif ($time <= 1) {
            return 2;
        } elseif ($time <= 6) {
            return 3;
        } elseif ($time <= 12) {
            return 4;
        } elseif ($time <= 24) {
            return 5;
        } elseif ($time <= (5 * 12)) {
            return 6;
        } elseif ($time <= (10 * 12)) {
            return 7;
        } elseif ($time > (10 * 12)) {
            return 8;
        }

        return Choices::NO_INFORMATION;
    }

    protected function createEvalBudgetGroup(EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setContributionAmt((float) $this->field['Montant participation financière'])
            ->setResourcesGroupAmt((float) $this->field['Total ressources ménage'])
            ->setBudgetBalanceAmt((float) $this->field['Total ressources ménage'])
            ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(EvaluationGroup $evaluationGroup): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
        ->setHousingStatus($this->findInArray($this->field['Dispositif'], self::PLACE_HOUSING_STATUS))
        ->setSiaoRequest($this->findInArray($this->field['Demande SIAO active'], self::YES_NO))
        ->setSiaoRequestDate($this->field['Date demande initiale SIAO'] ? new \Datetime($this->field['Date demande initiale SIAO']) : null)
        ->setSiaoUpdatedRequestDate($this->field['Date dernière actualisation SIAO'] ? new \Datetime($this->field['Date dernière actualisation SIAO']) : null)
        ->setSocialHousingRequest($this->findInArray($this->field['Demande de logement social active'], self::YES_NO))
        ->setSocialHousingRequestDate($this->field['Date demande de logement social'] ? new \Datetime($this->field['Date demande de logement social']) : null)
        ->setCommentEvalHousing($this->field['Modalité de sortie vers le logement'] ? 'Modalité de sortie vers le logement : '.$this->field['Modalité de sortie vers le logement'] : null)
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalHousingGroup);

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
                $this->manager->persist($this->person);
                $this->person->addRolesPerson($this->createRolePerson($peopleGroup));
                $this->people[] = $this->person;
            }
        }

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
                 ->setHead($this->head)
                 ->setRole($this->role)
                 ->setPerson($this->person)
                 ->setPeopleGroup($peopleGroup);

        $this->manager->persist($rolePerson);

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
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }

    protected function createEvaluationPerson(EvaluationGroup $evaluationGroup, SupportPerson $supportPerson): EvaluationPerson
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson)
            ->setInitEvalPerson($this->createInitEvalPerson($supportPerson))
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->manager->persist($evaluationPerson);

        $this->createEvalSocialPerson($evaluationPerson);
        $this->createEvalAdmPerson($evaluationPerson);
        $this->createEvalBudgetPerson($evaluationPerson);
        $this->createEvalFamilyPerson($evaluationPerson);
        $this->createEvalProfPerson($evaluationPerson);

        return $evaluationPerson;
    }

    protected function createInitEvalPerson(SupportPerson $supportPerson): ?InitEvalPerson
    {
        $initEvalPerson = (new InitEvalPerson())
            ->setPaper($this->findInArray($this->field['Situation administrative (entrée)'], self::PAPER))
            ->setPaperType($this->findInArray($this->field['Situation administrative (entrée)'], self::PAPER_TYPE))
            ->setRightSocialSecurity($this->findInArray($this->field['Couverture maladie (entrée)'], self::RIGHT_SOCIAL_SECURITY))
            ->setSocialSecurity($this->findInArray($this->field['Couverture maladie (entrée)'], self::SOCIAL_SECURITY))
            ->setFamilyBreakdown($this->findInArray($this->field['Rupture liens familiaux et amicaux'], self::YES_NO))
            ->setFriendshipBreakdown($this->findInArray($this->field['Rupture liens familiaux et amicaux'], self::YES_NO))
            ->setProfStatus($this->findInArray($this->field['Emploi (entrée)'], self::PROF_STATUS))
            ->setContractType($this->findInArray($this->field['Emploi (entrée)'], self::CONTRACT_TYPE))
            ->setResources($this->findInArray($this->field['Ressources (entrée)'], self::YES_NO))
            ->setResourcesAmt((float) $this->field['Montant ressources (entrée)'])
            ->setUnemplBenefit('Oui' === $this->field['ARE (entrée)'] ? Choices::YES : 0)
            ->setMinimumIncome('Oui' === $this->field['RSA (entrée)'] ? Choices::YES : 0)
            ->setFamilyAllowance('Oui' === $this->field['AF (entrée)'] ? Choices::YES : 0)
            ->setSalary('Oui' === $this->field['Salaire (entrée)'] ? Choices::YES : 0)
            ->setRessourceOther($this->field['Autres ressources (entrée)'] ? Choices::YES : 0)
            ->setRessourceOtherPrecision($this->field['Autres ressources (entrée)'])
            ->setDebts(Choices::NO_INFORMATION)
            ->setSalaryAmt('Oui' === $this->field['Salaire (entrée)'] && 'Oui' != $this->field['ARE (entrée)'] && 'Oui' != $this->field['RSA (entrée)'] && 'Oui' != $this->field['AF (entrée)'] && !$this->field['Autres ressources (entrée)'] ? (float) $this->field['Montant ressources (entrée)'] : null)
            ->setUnemplBenefitAmt('Oui' === $this->field['ARE (entrée)'] && 'Oui' != $this->field['Salaire (entrée)'] && 'Oui' != $this->field['RSA (entrée)'] && 'Oui' != $this->field['AF (entrée)'] && !$this->field['Autres ressources (entrée)'] ? (float) $this->field['Montant ressources (entrée)'] : null)
            ->setMinimumIncomeAmt('Oui' === $this->field['RSA (entrée)'] && 'Oui' != $this->field['Salaire (entrée)'] && 'Oui' != $this->field['ARE (entrée)'] && 'Oui' != $this->field['AF (entrée)'] && !$this->field['Autres ressources (entrée)'] ? (float) $this->field['Montant ressources (entrée)'] : null)
            ->setFamilyAllowanceAmt('Oui' === $this->field['AF (entrée)'] && 'Oui' != $this->field['Salaire (entrée)'] && 'Oui' != $this->field['RSA (entrée)'] && 'Oui' != $this->field['ARE (entrée)'] && !$this->field['Autres ressources (entrée)'] ? (float) $this->field['Montant ressources (entrée)'] : null)
            ->setRessourceOtherAmt($this->field['Autres ressources (entrée)'] && 'Oui' != $this->field['Salaire (entrée)'] && 'Oui' != $this->field['RSA (entrée)'] && 'Oui' != $this->field['ARE (entrée)'] && 'Oui' != $this->field['AF (entrée)'] ? (float) $this->field['Montant ressources (entrée)'] : null)
            ->setComment($this->field['Commentaire situation à l\'entrée'])
            ->setSupportPerson($supportPerson);

        $initEvalPerson = $this->updateResourceType($initEvalPerson, $this->field['Autres ressources (entrée)']);

        $this->manager->persist($initEvalPerson);

        return $initEvalPerson;
    }

    protected function createEvalSocialPerson(EvaluationPerson $evaluationPerson)
    {
        $comment = '';

        if ($this->field['Spécificités autres']) {
            $comment = $comment.$this->field['Spécificités autres']."\n";
        }
        if ($this->field['Orientation vers les soins/santé']) {
            $comment = $comment.'Orientation vers les soins/santé : '.$this->field['Orientation vers les soins/santé']."\n";
        }

        $evalSocialPerson = (new EvalSocialPerson())
            ->setRightSocialSecurity($this->findInArray($this->field['Couverture maladie'], self::RIGHT_SOCIAL_SECURITY))
            ->setSocialSecurity($this->findInArray($this->field['Couverture maladie'], self::SOCIAL_SECURITY))
            ->setFamilyBreakdown($this->findInArray($this->field['Rupture liens familiaux et amicaux'], self::YES_NO))
            ->setFriendshipBreakdown($this->findInArray($this->field['Rupture liens familiaux et amicaux'], self::YES_NO))
            ->setChildWelfareBackground($this->findInArray($this->field['Parcours institutionnel enfance'], self::YES_NO))
            ->setHealthProblem(Choices::YES === $this->field['Problématique santé mentale'] || Choices::YES === $this->field['Problématique santé - Addiction'] ? Choices::YES : null)
            ->setMentalHealthProblem($this->findInArray($this->field['Problématique santé mentale'], self::YES_NO_BOOLEAN))
            ->setAddictionProblem($this->findInArray($this->field['Problématique santé - Addiction'], self::YES_NO_BOOLEAN))
            ->setHomeCareSupport($this->findInArray($this->field['Service soin ou acc. à domicile'], self::CARE_SUPPORT))
            ->setHomeCareSupportType($this->findInArray($this->field['Service soin ou acc. à domicile'], self::CARE_SUPPORT_TYPE))
            ->setCommentEvalSocialPerson($comment)
            ->setEvaluationPerson($evaluationPerson);

        $this->manager->persist($evalSocialPerson);
    }

    protected function createEvalFamilyPerson(EvaluationPerson $evaluationPerson): ?EvalFamilyPerson
    {
        if (!$this->field['Grossesse'] && !$this->field['Mode garde'] && !$this->field['Mesure de protection']) {
            return null;
        }

        $evalFamilyPerson = (new EvalFamilyPerson())
            ->setUnbornChild($this->findInArray($this->field['Grossesse'], self::YES_NO))
            ->setChildcareSchoolType($this->findInArray($this->field['Mode garde'], self::CHILDCARE_SCHOOL))
            ->setProtectiveMeasure($this->findInArray($this->field['Mesure de protection'], self::PROTECTIVE_MEASURE))
            ->setProtectiveMeasureType($this->findInArray($this->field['Mesure de protection'], self::PROTECTIVE_MEASURE_TYPE))
            ->setEvaluationPerson($evaluationPerson);

        $this->manager->persist($evalFamilyPerson);

        return $evalFamilyPerson;
    }

    protected function createEvalAdmPerson(EvaluationPerson $evaluationPerson): ?EvalAdmPerson
    {
        if (!$this->field['Nationalité'] && !$this->field['Situation administrative']) {
            return null;
        }

        $evalAdmPerson = (new EvalAdmPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setNationality($this->findInArray($this->field['Nationalité'], self::NATIONALITY))
            ->setCountry($this->field['Pays d\'origine'])
            ->setPaper($this->findInArray($this->field['Situation administrative'], self::PAPER))
            ->setPaperType($this->findInArray($this->field['Situation administrative'], self::PAPER_TYPE))
            ->setAsylumBackground($this->findInArray($this->field['Parcours asile'], self::YES_NO))
            ->setCommentEvalAdmPerson(97 === $this->findInArray($this->field['Situation administrative'], self::PAPER_TYPE) ? $this->field['Situation administrative'] : null);

        $this->manager->persist($evalAdmPerson);

        return $evalAdmPerson;
    }

    protected function createEvalProfPerson(EvaluationPerson $evaluationPerson): ?EvalProfPerson
    {
        if ((float) $this->field['Âge'] < 16 || !$this->field['Emploi']) {
            return null;
        }

        $evalProfPerson = (new EvalProfPerson())
            ->setCommentEvalProf($this->field['Orientation vers l\'emploi'] ? 'Orientation vers l\'emploi : '.$this->field['Orientation vers l\'emploi'] : null)
            ->setProfStatus($this->findInArray($this->field['Emploi'], self::PROF_STATUS))
            ->setContractType($this->findInArray($this->field['Emploi'], self::CONTRACT_TYPE))
            ->setEvaluationPerson($evaluationPerson);

        $this->manager->persist($evalProfPerson);

        return $evalProfPerson;
    }

    protected function createEvalBudgetPerson(EvaluationPerson $evaluationPerson): ?EvalBudgetPerson
    {
        if ((float) $this->field['Âge'] < 16 || !$this->field['Ressources']) {
            return null;
        }

        $evalBudgetPerson = (new EvalBudgetPerson())
            ->setResources($this->findInArray($this->field['Ressources'], self::YES_NO))
            ->setResourcesAmt((float) $this->field['Montant ressources'])
            ->setUnemplBenefit('Oui' === $this->field['ARE'] ? Choices::YES : 0)
            ->setMinimumIncome('Oui' === $this->field['RSA'] ? Choices::YES : 0)
            ->setFamilyAllowance('Oui' === $this->field['AF'] ? Choices::YES : 0)
            ->setSalary('Oui' === $this->field['Salaire'] ? Choices::YES : 0)
            ->setRessourceOther($this->field['Autres ressources'] ? Choices::YES : 0)
            ->setRessourceOtherPrecision($this->field['Autres ressources'])
            ->setSalaryAmt('Oui' === $this->field['Salaire'] && 'Oui' != $this->field['ARE'] && 'Oui' != $this->field['RSA'] && 'Oui' != $this->field['AF'] && !$this->field['Autres ressources'] ? (float) $this->field['Montant ressources'] : null)
            ->setUnemplBenefitAmt('Oui' === $this->field['ARE'] && 'Oui' != $this->field['Salaire'] && 'Oui' != $this->field['RSA'] && 'Oui' != $this->field['AF'] && !$this->field['Autres ressources'] ? (float) $this->field['Montant ressources'] : null)
            ->setMinimumIncomeAmt('Oui' === $this->field['RSA'] && 'Oui' != $this->field['Salaire'] && 'Oui' != $this->field['ARE'] && 'Oui' != $this->field['AF'] && !$this->field['Autres ressources'] ? (float) $this->field['Montant ressources'] : null)
            ->setFamilyAllowanceAmt('Oui' === $this->field['AF'] && 'Oui' != $this->field['Salaire'] && 'Oui' != $this->field['RSA'] && 'Oui' != $this->field['ARE'] && !$this->field['Autres ressources'] ? (float) $this->field['Montant ressources'] : null)
            ->setRessourceOtherAmt($this->field['Autres ressources'] && 'Oui' != $this->field['Salaire'] && 'Oui' != $this->field['RSA'] && 'Oui' != $this->field['ARE'] && 'Oui' != $this->field['AF'] ? (float) $this->field['Montant ressources'] : null)
            ->setEvaluationPerson($evaluationPerson);

        $evalBudgetPerson = $this->updateResourceType($evalBudgetPerson, $this->field['Autres ressources']);

        $this->manager->persist($evalBudgetPerson);

        return $evalBudgetPerson;
    }

    /**
     * @param EvalBudgetPerson|InitEvalPerson $evalBudgetPerson
     */
    protected function updateResourceType(object $evalBudgetPerson, string $resourceType): object
    {
        return $evalBudgetPerson
            ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
            ->setDisChildAllowance(strstr($resourceType, 'AAEH') ? Choices::YES : 0)
            ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
            ->setTempWaitingAllowance(strstr($resourceType, 'ATA') ? Choices::YES : 0)
            ->setPensionBenefit(strstr($resourceType, 'Pension de retraite') ? Choices::YES : 0)
            ->setMaintenance(strstr($resourceType, 'Pension alimentaire') ? Choices::YES : 0)
            ->setAsf(strstr($resourceType, 'ASF') ? Choices::YES : 0)
            ->setSolidarityAllowance(strstr($resourceType, 'ASS') ? Choices::YES : 0)
            ->setPaidTraining(strstr($resourceType, 'Formation') ? Choices::YES : 0)
            ->setYouthGuarantee(strstr($resourceType, 'Garantie jeunes') ? Choices::YES : 0)
            ->setDisabilityPension(strstr($resourceType, 'Pension d\'invalidité') ? Choices::YES : 0)
            ->setPaje(strstr($resourceType, 'PAJE') ? Choices::YES : 0)
            ->setActivityBonus(strstr($resourceType, 'Prime d\'activité') ? Choices::YES : 0);
    }

    protected function getRoleAndGender(int $typology)
    {
        $this->gender = Choices::NO_INFORMATION;
        $this->head = false;
        $this->role = 97;

        if (' DP' === $this->field['Rôle']) {
            $this->head = true;
            if (in_array($typology, [1, 4])) {
                $this->gender = Person::GENDER_FEMALE;
            }
            if (in_array($typology, [2, 5])) {
                $this->gender = Person::GENDER_MALE;
            }
            if (in_array($typology, [1, 2])) {
                $this->role = 5;
            } elseif (in_array($typology, [4, 5])) {
                $this->role = 4;
            } elseif (in_array($typology, [3, 6, 7, 8])) {
                $this->role = 1;
            }
        } elseif ('Enfant' === $this->field['Rôle']) {
            $this->role = RolePerson::ROLE_CHILD;
        } elseif ('Conjoint·e' === $this->field['Rôle']) {
            $this->role = 1;
        }
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

    protected function createReferent(PeopleGroup $peopleGroup): ?Referent
    {
        if (!$this->field['Référent social']) {
            return null;
        }
        $referent = (new Referent())
            ->setName($this->field['Référent social'])
            ->setType($this->getTypeReferent())
            ->setSocialWorker($this->field['Référent social'])
            ->setComment(null)
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($referent);

        return $referent;
    }

    protected function getTypeReferent()
    {
        $referent = $this->field['Référent social'];

        if (strstr($referent, 'SSD')
            || strstr($referent, 'antenne')
            || strstr($referent, 'Antenne')
            || strstr($referent, 'Conseil Départemental')
        ) {
            return 6;
        }
        if (strstr($referent, 'CCAS')) {
            return 4;
        }

        return Choices::NO_INFORMATION;
    }

    protected function getPlace(): Place
    {
        $placeExists = false;

        foreach ($this->places as $key => $value) {
            if ((string) $key === $this->field['Nom place']) {
                $placeExists = true;
            }
        }

        if (!$placeExists) {
            $this->places[(string) $this->field['Nom place']] = [
                $this->createPlace($this->device),
            ];
        }

        return $this->places[$this->field['Nom place']][0];
    }

    protected function createPlace(Device $device): Place
    {
        $place = new Place();
        $place->setConfiguration($this->findInArray($this->field['Configuration'], self::CONFIGURATION))
            ->setIndividualCollective($this->findInArray($this->field['Individuel ou partagé'], self::INDIVIDUAL_COLLECTIVE))
            ->setName($this->field['Nom place'])
            ->setAddress(isset($this->field['Adresse place']) ? (string) $this->field['Adresse place'] : (string) $this->field['Adresse logement'])
            ->setNbPlaces(isset($this->field['Nb places']) ? (int) $this->field['Nb places'] : (int) $this->field['Nb personnes'])
            ->setStartDate(isset($this->field['Date ouverture']) ? new \Datetime($this->field['Date ouverture']) : new \Datetime('2020-01-01'))
            ->setPlaceType($this->findInArray($this->field['Type place'], self::PLACE_TYPE))
            ->setArea(isset($this->field['Superficie']) ? (float) $this->field['Superficie'] : null)
            ->setLessor(isset($this->field['Bailleur']) ? $this->field['Bailleur'] : null)
            ->setDevice($device)
            ->setService($this->service)
            ->setSubService($this->getSubService())
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        if (2 === $place->getConfiguration()) {
            $place->setAddress($this->service->getAddress())
                ->setCity($this->service->getCity())
                ->setZipcode($this->service->getZipcode());
        }

        $this->manager->persist($place);

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
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->manager->persist($placeGroup);

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
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->manager->persist($placePerson);

        return $placePerson;
    }

    protected function getUsers(): array
    {
        $users = [];

        foreach ($this->service->getUsers() as $user) {
            foreach (self::SOCIAL_WORKER as $name) {
                if (strstr($name, $user->getLastname())) {
                    $users[$name] = $user;
                }
            }
        }

        return $users;
    }
}
