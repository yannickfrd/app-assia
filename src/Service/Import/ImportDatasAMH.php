<?php

namespace App\Service\Import;

use App\Entity\Accommodation;
use App\Entity\AccommodationGroup;
use App\Entity\Device;
use App\Entity\EvalAdmPerson;
use App\Entity\EvalBudgetGroup;
use App\Entity\EvalBudgetPerson;
use App\Entity\EvalFamilyGroup;
use App\Entity\EvalFamilyPerson;
use App\Entity\EvalHousingGroup;
use App\Entity\EvalProfPerson;
use App\Entity\EvalSocialGroup;
use App\Entity\EvalSocialPerson;
use App\Entity\EvaluationGroup;
use App\Entity\EvaluationPerson;
use App\Entity\HotelSupport;
use App\Entity\InitEvalGroup;
use App\Entity\InitEvalPerson;
use App\Entity\PeopleGroup;
use App\Entity\Person;
use App\Entity\Referent;
use App\Entity\RolePerson;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Entity\User;
use App\Form\Utils\Choices;
use App\Notification\MailNotification;
use App\Repository\AccommodationRepository;
use App\Repository\DeviceRepository;
use App\Repository\PersonRepository;
use App\Repository\SubServiceRepository;
use App\Service\Phone;
use Doctrine\ORM\EntityManagerInterface;

class ImportDatasAMH extends ImportDatas
{
    public const SOCIAL_WORKER = [
        'Marie-Laure PEBORDE',
        'Camille RAVEZ',
        'Typhaine PECHE',
        'Cécile BAZIN',
        'Nathalie POULIQUEN',
        'Marina DJORDJEVIC',
        'Melody ROMET',
        'Gaëlle PRINCET',
        'Marion FRANCOIS',
        'Margot COURAUDON',
        'Marilyse TOURNIER',
        'Rozenn DOUELE ZAHAR',
        'Laurine VIALLE',
        'Ophélie QUENEL',
        'Camille GALAN',
        'Christine VESTUR',
        'Julie MARTIN',
    ];

    public const YES_NO = [
        'Oui' => 1,
        'Non' => 2,
        'En cours' => 3,
        'NR' => 99,
    ];

    public const YES_NO_BOOLEAN = [
        'Non' => 0,
        'Oui' => 1,
        '' => 0,
    ];

    public const HEAD = [
        'Oui' => true,
        '' => false,
    ];

    public const GENDER = [
        'Femme' => Person::GENDER_FEMALE, // A Vérifier
        'Homme' => Person::GENDER_MALE, // A Vérifier
        '' => 99,
    ];

    public const ROLE = [
        'Personne isolée' => 5,
        'Parent isolé' => 4,
        'Epoux(se)' => 2,
        'Concubin(e)' => 1,
        'Concubin(€)' => 1,
        'Membre famille' => 6,
        'Enfant' => 3,
        'Autre' => 97,
        '' => 99,
    ];

    public const FAMILY_TYPOLOGY = [
        'Femme isolée' => 1,
        'Homme isolé' => 2,
        'Couple sans enfant' => 3,
        'Couple avec enfant(s)' => 6,
        'Femme seule avec enfant(s)' => 4,
        'Homme seul avec enfant(s)' => 5,
        'Groupe d\'adultes avec enfant(s)' => 8,
        'Groupe d\'adultes sans enfant' => 7,
        'Mineur isolé' => 9,
        ];

    public const MARITAL_STATUS = [
        'Célibataire' => 1,
        'Concubinage' => 2,
        'Divorcé' => 3,
        'Marié' => 4,
        'Séparé' => 6,
        'Veuf' => 7,
        'Vie maritale' => 8,
        'Pacsé' => 5,
        'NR' => 99,
    ];

    public const REASON_REQUEST = [
        'Absence de ressources' => 1,
        'Arrivée en France' => 4,
        'Départ du département initial' => 1,
        'Dort dans la rue' => 3,
        'Expulsion locative' => 9,
        'Fin de prise en charge ASE' => 10,
        'Fin d\'hébergement chez des tiers' => 11,
        'Fin d\'hospitalisation' => 12,
        'Fin prise en charge Conseil général' => 13,
        'Inadaptation du logement' => 15,
        'Logement insalubre' => 16,
        'Regroupement familial' => 19,
        'Risque d\'expulsion locative' => 20,
        'Séparation ou rupture des liens familiaux' => 21,
        'Sortie d\'hébergement' => 24,
        'Sortie dispositif asile' => 5,
        'Violences familiales-conjugales' => 27,
        'Autre' => 97,
    ];

    public const NATIONALITY = [
        'Française' => 1,
        'UE' => 2,
        'Hors UE' => 3,
        'Apatride' => 4,
    ];

    public const PAPER = [
        'Autorisation Provisoire de Séjour' => 1,
        'Carte de résident (10 ans)' => 1,
        'Carte de séjour temporaire' => 1,
        'Carte d\'identité européenne' => 1,
        'CNI' => 1,
        'Débouté du droit d\'asile' => 2,
        'Demandeur d\'asile' => 1,
        'Démarche en cours' => 3,
        'OQTF' => 2,
        'Récépissé asile' => 1,
        'Récépissé de 1ère demande' => 1,
        'Récépissé renouvellement de titre' => 1,
        'Réfugié' => 1,
        'Sans titre de séjour' => 2,
        'Titre de séjour "vie privée et familiale"' => 1,
        'Titre de séjour pour Soins' => 1,
        'Visa de court séjour' => 1,
        'Visa de long séjour' => 1,
        'NR' => 99,
    ];

    public const PAPER_TYPE = [
        'Autorisation Provisoire de Séjour' => 22,
        'Carte de résident (10 ans)' => 20,
        'Carte de séjour temporaire' => 21,
        'Carte d\'identité européenne' => 03,
        'CNI' => 01,
        'Débouté du droit d\'asile' => 99,
        'Demandeur d\'asile' => null,
        'Démarche en cours' => null,
        'OQTF' => null,
        'Récépissé asile' => 30,
        'Récépissé de 1ère demande' => 30,
        'Récépissé renouvellement de titre' => 31,
        'Réfugié' => 20,
        'Sans titre de séjour' => null,
        'Titre de séjour "vie privée et familiale"' => 21,
        'Titre de séjour pour Soins' => 21,
        'Visa de court séjour' => 97,
        'Visa de long séjour' => 97,
        'NR' => 1,
    ];

    public const ASYLUM_BACKGROUND = [
        'Autorisation Provisoire de Séjour' => 2,
        'Carte de résident (10 ans)' => null,
        'Carte de séjour temporaire' => 2,
        'Carte d\'identité européenne' => null,
        'CNI' => null,
        'Débouté du droit d\'asile' => 1,
        'Demandeur d\'asile' => 1,
        'Démarche en cours' => null,
        'OQTF' => null,
        'Récépissé asile' => 1,
        'Récépissé de 1ère demande' => 2,
        'Récépissé renouvellement de titre' => 2,
        'Réfugié' => 1,
        'Sans titre de séjour' => null,
        'Titre de séjour "vie privée et familiale"' => 2,
        'Titre de séjour pour Soins' => null,
        'Visa de court séjour' => 2,
        'Visa de long séjour' => 2,
        'NR' => null,
    ];

    public const RIGHT_TO_RESIDE = [
        'Autorisation Provisoire de Séjour' => null,
        'Carte de résident (10 ans)' => null,
        'Carte de séjour temporaire' => null,
        'Carte d\'identité européenne' => null,
        'CNI' => null,
        'Débouté du droit d\'asile' => 1,
        'Demandeur d\'asile' => 2,
        'Démarche en cours' => null,
        'OQTF' => null,
        'Récépissé asile' => 2,
        'Récépissé de 1ère demande' => null,
        'Récépissé renouvellement de titre' => null,
        'Réfugié' => 4,
        'Sans titre de séjour' => null,
        'Titre de séjour "vie privée et familiale"' => null,
        'Titre de séjour pour Soins' => null,
        'Visa de court séjour' => null,
        'Visa de long séjour' => null,
        'NR' => null,
    ];

    public const RIGHT_SOCIAL_SECURITY = [
        'Sans' => 2,
        'En cours' => 3,
        'Non' => 2,
        'CMU' => 1,
        'CMU C' => 1,
        'CMU-C' => 1,
        'CMUC' => 1,
        'PUMA' => 1,
        'AME' => 1,
        'Régime Général' => 1,
        'Régime général' => 1,
        'Mutuelle' => 1,
        'mutuelle' => 1,
        'ACS' => 1,
        'CSS' => 1,
        'CSC' => 1,
        'Autre' => 1,
        'NR' => 99,
    ];

    public const SOCIAL_SECURITY = [
        'Régime Général' => 1,
        'Régime général' => 1,
        'Mutuelle' => 2,
        'mutuelle' => 2,
        'CMU' => 4,
        'CMU C' => 4,
        'CMU-C' => 4,
        'CMUC' => 4,
        'PUMA' => 4,
        'AME' => 5,
        'ACS' => 6,
        'CSS' => 4,
        'CSC' => 4,
        'Autre' => 97,
    ];

    public const PROF_STATUS = [
        'CDD' => 8,
        'CDI' => 8,
        'Intérim' => 8,
        'Apprentissage' => 3,
        'Formation' => 3,
        'Indépendant' => 6,
        'Auto-entrepreneur' => 1,
        'CDD; CDI' => 8,
        'CDD; Intérim' => 8,
        'CDI; Intérim' => 8,
    ];

    public const CONTRACT_TYPE = [
        'CDD' => 1,
        'CDI' => 2,
        'Intérim' => 7,
        'Apprentissage' => 4,
        'Formation' => null,
        'Indépendant' => null,
        'Auto-entrepreneur' => null,
        'CDD; CDI' => 2,
        'CDD; Intérim' => 2,
        'CDI; Intérim' => 2,
    ];

    public const RESOURCES = [
        'Oui' => 1,
        'Non' => 2,
        'Ouverture de droit en cours' => 3,
        'Droits suspendus' => 4,
        'NR' => 99,
    ];

    public const RESOURCES_TYPE = [
        'RSA' => 1,
        'Assedic' => 1,
        'ARE' => 1,
        'AF' => 1,
        'AAH' => 1,
        'AAEH' => 1,
        'ADA/ATA' => 1,
        'Pension de retraite' => 1,
        'Formation' => 1,
        'ASS' => 1,
        'PAJE' => 1,
        'Pension alimentaire' => 1,
        'Salaire' => 1,
        'Garantie Jeunes' => 1,
        'Prime d\'activité' => 1,
        'Pension d\'invalidité' => 1,
        'Indemnités journalières' => 1,
        'Autre' => 1,
        'Bourse d’étude' => 1,
        '' => 99,
    ];

    public const DEBTS_TYPE = [
        'Loyer' => 1,
        'Consommation' => 1,
        'Découvert bancaire' => 1,
        'Energie' => 1,
        'Dommages et intérêts' => 1,
        'Remise en état du logement' => 1,
        'Santé' => 1,
        'Autre' => 1,
    ];

    public const OVER_INDEBT_RECORD = [
        'Non' => 2,
        'Dossier à déposer' => 2,
        'Dossier déposé' => 3,
        'Dossier recevable' => 1,
        'Dossier refusé' => 2,
        'Moratoire en cours' => 1,
        'Plan apurement' => 1,
        'Autre' => 99,
        'NR' => 99,
    ];

    public const SETTLEMENT_PLAN = [
        'Non' => null,
        'Dossier à déposer' => null,
        'Dossier déposé' => null,
        'Dossier recevable' => null,
        'Dossier refusé' => null,
        'Moratoire en cours' => null,
        'Plan apurement' => 2,
        'Autre' => 99,
        'NR' => 99,
    ];

    public const DLS = [
        'OUI' => 1,
        'OUI - PROPO EN COURS' => 1,
        'NON' => 2,
        'NON - RADIE' => 2,
        'NON - NUR / NOM INTROUVABLE' => 2,
    ];

    public const SIAO = [
        'OUI' => 1,
        'NON' => 2,
    ];

    public const SIAO_DEPARTMENTS = [
        'SIAO 75' => 75,
        'SIAO 77' => 77,
        'SIAO 78' => 78,
        'SIAO 92' => 92,
        'SIAO 93' => 93,
        'SIAO 94' => 94,
        'SIAO 95' => 95,
    ];
    public const DEPARTMENTS = [
        '75' => 75,
        '77' => 77,
        '78' => 78,
        '92' => 92,
        '93' => 93,
        '94' => 94,
        '95' => 95,
    ];

    public const RECOMMENDATION = [
    'Hébergement' => 10,
    'Logement Intermédiaire' => 20,
    // 'Sorti d\'hôtel' => 10,
    'Logement' => 30,
    'Logement ' => 30,
    ];

    public const RECOMMENDATION_TYPE = [
        'CHU' => 10,
        'CHS' => 10,
        'CHRS' => 10,
        'ALTHO' => 20,
        'HUAS' => 10,
        'Résidence Sociale' => 20,
        'FJT' => 20,
        'RJA' => 20,
        'FTM' => 20,
        'Solibail' => 20,
        'Pension de famille' => 20,
        'ALT' => 20,
        'Logement droit commun' => 30,
        'RHVS' => 20,
        'Autre' => 10,
    ];

    public const DALO_COMMISSION = [
        'DAHO - Prioritaire' => 1,
        'DAHO - TA' => 1,
        'DAHO - Caduc' => 2,
        'DALO - Prioritaire' => 1,
        'DALO - TA' => 1,
        'DALO - Caduc' => 2,
    ];

    public const DALO_REQUALIFIED_DAHO = [
        'DAHO - Prioritaire' => 1,
        'DAHO - TA' => 1,
        'DAHO - Caduc' => null,
        'DALO - Prioritaire' => 2,
        'DALO - TA' => 2,
        'DALO - Caduc' => null,
    ];

    public const DALO_TRIBUNAL_ACTION = [
        'DAHO - Prioritaire' => 2,
        'DAHO - TA' => 1,
        'DAHO - Caduc' => null,
        'DALO - Prioritaire' => 2,
        'DALO - TA' => 1,
        'DALO - Caduc' => 2,
    ];

    public const END_STATUS = [
        'Non respect de la convention AMH' => 99,
        'Fin de prise en charge ASE' => 99,
        'HU en CHU' => 102,
        'HU en CHRS' => 102,
        'Stab en CHU' => 103,
        'Stab en CHRS' => 103,
        'Insertion en CHRS' => 104,
        'Résidence sociale' => 204,
        'Logement intermédiaire' => 204,
        'ALTHO' => 208,
        'Solibail' => 206,
        'Logement parc public' => 301,
        'Logement parc privé' => 300,
        'ALT' => 200,
        'HUDA' => 401,
        'CADA' => 400,
        'Hébergé par famille/tiers' => 011,
        'Prise en charge ASE' => 99,
        'FTM' => 202,
        'Autre' => 97,
    ];

    public const END_SUPPORT_REASON = [
        'Non respect de la convention AMH' => 2,
        'Fin de prise en charge ASE' => 3,
        'HU en CHU' => 1,
        'HU en CHRS' => 1,
        'Stab en CHU' => 1,
        'Stab en CHRS' => 1,
        'Insertion en CHRS' => 1,
        'Résidence sociale' => 1,
        'Logement intermédiaire' => 1,
        'ALTHO' => 1,
        'Solibail' => 1,
        'Logement parc public' => 1,
        'Logement parc privé' => 1,
        'ALT' => 1,
        'HUDA' => 1,
        'CADA' => 1,
        'Hébergé par famille/tiers' => 1,
        'Prise en charge ASE' => 3,
        'FTM' => 1,
        'Autre' => 97,
    ];

    public const SECTEUR = [
        'Cergy-Pontoise' => 1,
        'Plaine de France' => 1,
        'Rives de Seine' => 1,
        'Vallée de Montmorency' => 1,
        'Pays de France' => 1,
        'Vexin' => 1,
        'Plaine de France' => 1,
    ];

    public const CHILD_TO_HOST = [
        'Présence permanente' => 1,
        'Garde alterné' => 2,
        'Garde partagée' => 2,
        'Autre' => 97,
    ];

    protected $manager;
    protected $notification;

    protected $repoSubService;
    protected $repoDevice;
    protected $repoAccommodation;
    protected $repoPerson;

    protected $datas;
    protected $row;

    protected $fields;
    protected $field;

    protected $service;

    protected $deviceAMH;
    protected $deviceASEMab;
    protected $deviceASEHeb;
    protected $deviceINJ;
    protected $deviceHotelSupport;

    protected $localities;
    protected $hotels = [];

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
        MailNotification $notification,
        SubServiceRepository $repoSubService,
        DeviceRepository $repoDevice,
        AccommodationRepository $repoAccommodation,
        PersonRepository $repoPerson)
    {
        $this->manager = $manager;
        $this->notification = $notification;
        $this->repoSubService = $repoSubService;
        $this->repoDevice = $repoDevice;
        $this->repoAccommodation = $repoAccommodation;
        $this->repoPerson = $repoPerson;
    }

    public function importInDatabase(string $fileName, Service $service): int
    {
        $this->fields = $this->getDatas($fileName);
        $this->service = $service;

        $this->deviceAMH = $this->repoDevice->find(Device::HOTEL_AMH); // Famille AMH
        $this->deviceASEMab = $this->repoDevice->find(Device::ASE_MAB); // ASE Mise à l'abri
        $this->deviceASEHeb = $this->repoDevice->find(Device::ASE_HEB); // ASE Hébergement
        $this->deviceINJ = $this->repoDevice->find(Device::HOTEL_INJ); // Injonctions
        $this->deviceHotelSupport = $this->repoDevice->find(Device::HOTEL_SUPPORT); // Accompagnement hôtel
        $this->localities = $this->getLocalities();
        $this->hotels = $this->getHotels();
        $this->users = $this->getUsers();

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $typology = $this->findInArray($this->field['Compo'], self::FAMILY_TYPOLOGY) ?? 9;

                $this->getRole($typology);
                $this->person = $this->getPerson();
                $this->personExists = $this->personExistsInDatabase($this->person);

                $this->checkGroupExists($typology);

                $this->person = $this->createPerson($this->items[$this->field['ID_ménage']]['peopleGroup']);

                $support = $this->items[$this->field['ID_ménage']]['supports'][$this->field['ID_AMH']];
                $supportGroup = $support['support'];
                $evaluationGroup = $support['evaluation'];

                $supportPerson = $this->createSupportPerson($supportGroup);
                $this->createEvaluationPerson($evaluationGroup, $supportPerson);

                if ('Oui' === $this->field['DP']) {
                    $this->createNote($supportGroup, 'Notes ACCESS', $this->field['Notes']);
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

        return count($this->items);
    }

    protected function getPerson()
    {
        $person = (new Person())
                ->setLastname($this->field['Nom'])
                ->setFirstname($this->field['Prénom'])
                ->setBirthdate($this->field['Date naissance'] ? new \Datetime($this->field['Date naissance']) : null)
                ->setGender($this->findInArray($this->field['Sexe'], self::GENDER) ?? 99)
                ->setSiSiaoId((int) $this->field['Id Personne'])
                ->setCreatedBy($this->user)
                ->setUpdatedBy($this->user);

        if ('Oui' === $this->field['DP']) {
            $person->setPhone1(strlen($this->field['Téléphone']) <= 15 ? Phone::formatPhone($this->field['Téléphone']) : null);
        }

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
            $this->createAccommodationGroup($peopleGroup, $supportGroup);
            $this->createReferent($peopleGroup);
            $evaluationGroup = $this->createEvaluationGroup($supportGroup);

            // On ajoute le groupe et le suivi dans le tableau associatif.
            $this->items[$this->field['ID_ménage']] = [
                'peopleGroup' => $peopleGroup,
                'supports' => [
                    $this->field['ID_AMH'] => [
                        'support' => $supportGroup,
                        'evaluation' => $evaluationGroup,
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
            if ($key === $this->field['ID_ménage']) {
                $groupExists = true;

                $supports = $this->items[$this->field['ID_ménage']]['supports'];

                $supportExists = false;
                // Vérifie si le suivi du groupe de la personne a déjà été créé.
                foreach ($supports as $key => $value) {
                    if ($key === $this->field['ID_AMH']) {
                        $supportExists = true;
                    }
                }

                // Si le suivi social du groupe n'existe pas encore, on le crée ainsi que l'évaluation sociale.
                if (false === $supportExists) {
                    $supportGroup = $this->createSupportGroup($this->items[$this->field['ID_ménage']]['peopleGroup']);
                    $evaluationGroup = $this->createEvaluationGroup($supportGroup);

                    $this->items[$this->field['ID_ménage']]['supports'][$this->field['ID_AMH']] = [
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
        if ('Oui' === $this->field['DP']) {
            $this->personExistsInDatabase();
        }

        $peopleGroup = (new PeopleGroup())
                    ->setFamilyTypology($typology)
                    ->setNbPeople((int) $this->field['Nb personnes'])
                    ->setSiSiaoId((int) $this->field['Id Groupe'])
                    ->setComment($this->field['Téléphone'])
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($peopleGroup);

        return $peopleGroup;
    }

    protected function createSupportGroup(PeopleGroup $peopleGroup): SupportGroup
    {
        $comment = '';

        $userReferent = $this->getUserReferent();

        if ($this->field['Date premier appel 115']) {
            $comment = 'Date premier appel 115 : '.(new \Datetime($this->field['Date premier appel 115']))->format('d/m/Y');
        }

        if (!$userReferent && $this->field['TS AMH']) {
            $comment = $comment."\nTS : ".$this->field['TS AMH'];
        }

        $device = $this->getDevice();

        $supportGroup = (new SupportGroup())
                    ->setService($this->service)
                    ->setSubService($this->field['Secteur'] ? $this->localities[$this->field['Secteur']] : null)
                    ->setDevice($device)
                    ->setReferent($userReferent)
                    ->setStatus($this->getStatus($this->field))
                    ->setStartDate($this->getStartDate($this->field))
                    ->setEndDate($this->getEndDate($this->field))
                    ->setEndStatus($this->findInArray($this->field['Type sortie AMH'], self::END_STATUS) ?? null)
                    ->setEndStatusComment($this->field['Précision motif sortie'])
                    ->setNbPeople((int) $this->field['Nb personnes'])
                    ->setCoefficient($device->getCoefficient())
                    ->setComment($comment)
                    ->setPeopleGroup($peopleGroup)
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($supportGroup);

        $this->createHotelSupport($supportGroup);

        return $supportGroup;
    }

    protected function getDevice(): Device
    {
        if ('En cours' === $this->field['Etat suivi AMH'] && 'Familles avec AMH' === $this->field['Dispositif']) {
            return $this->deviceHotelSupport;
        }

        switch ($this->field['Dispositif']) {
            case "ASE mise à l'abri":
                return $this->deviceASEMab;
                break;
            case 'ASE hébergement':
                return $this->deviceASEHeb;
                break;
            case 'Injonctions Réfugiés':
                return $this->deviceINJ;
                break;
            default:
               return $this->deviceAMH;
                break;
        }
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
        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setInitEvalGroup($this->createInitEvalGroup($supportGroup))
            ->setDate($supportGroup->getCreatedAt())
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
            ->setHousingStatus(100)
            ->setSiaoRequest(!empty($this->field['Date demande initiale']) ? Choices::YES : Choices::NO)
            ->setSocialHousingRequest($this->findInArray($this->field['DLS'], self::YES_NO) ?? null)
            ->setResourcesGroupAmt((float) $this->field['Montant ressources'])
            ->setDebtsGroupAmt((float) $this->field['Montant dettes'])
            ->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        return $initEvalGroup;
    }

    protected function createEvalFamilyGroup(EvaluationGroup $evaluationGroup): ?EvalFamilyGroup
    {
        if ($this->field['Commentaire garde enfants']) {
            $evalFamilyGroup = (new EvalFamilyGroup())
            ->setCommentEvalFamilyGroup($this->field['Commentaire garde enfants'])
            ->setEvaluationGroup($evaluationGroup);

            return $evalFamilyGroup;
        }

        return null;
    }

    protected function createEvalSocialGroup(EvaluationGroup $evaluationGroup): ?EvalSocialGroup
    {
        if ($this->field['Motif demande'] || $this->field['Couverture sociale'] || $this->field['Spécificités']) {
            $evalSocialGroup = (new EvalSocialGroup())
                ->setReasonRequest($this->findInArray($this->field['Motif demande'], self::REASON_REQUEST) ?? 99)
                ->setCommentEvalSocialGroup($this->field['Couverture sociale'])
                ->setAnimal(strstr($this->field['Spécificités'], 'Présence animal') ? Choices::YES : null)
                ->setEvaluationGroup($evaluationGroup);

            $this->manager->persist($evalSocialGroup);

            return $evalSocialGroup;
        }

        return null;
    }

    protected function createEvalBudgetGroup(EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
        ->setResourcesGroupAmt((float) $this->field['Montant ressources'])
        ->setChargesGroupAmt((float) $this->field['Montant charges'])
        ->setDebtsGroupAmt((float) $this->field['Montant dettes'])
        ->setContributionAmt((float) $this->field['Montant PAF'])
        // ->setBudgetBalanceAmt((float) ($this->field['Montant ressources'] - $this->field['Montant charges']));
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(EvaluationGroup $evaluationGroup): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
        ->setHousingStatus(100)
        ->setSiaoRequest(!empty($this->field['Date demande initiale']) ? Choices::YES : Choices::NO)
        ->setSiaoRequestDate($this->field['Date demande initiale'] ? new \Datetime($this->field['Date demande initiale']) : null)
        ->setSiaoUpdatedRequestDate($this->field['Date réactu'] ? new \Datetime($this->field['Date réactu']) : null)
        ->setSiaoRequestDept($this->field['Date réactu'] ? 95 : null)
        ->setSiaoRecommendation($this->getSiaoRecomendation())
        ->setSocialHousingRequest($this->findInArray($this->field['DLS'], self::YES_NO) ?? null)
        ->setSocialHousingRequestId($this->field['NUR'])
        ->setSyplo($this->field['N°SYPLO'] || $this->field['Date SYPLO'] ? Choices::YES : null)
        ->setSyploDate($this->field['Date SYPLO'] ? new \Datetime($this->field['Date SYPLO']) : null)
        ->setSyploId($this->field['N°SYPLO'])
        ->setDaloAction($this->findInArray($this->field['DALO DAHO'], self::DALO_COMMISSION) ?? null)
        ->setDaloType($this->findInArray($this->field['DALO DAHO'], self::DALO_REQUALIFIED_DAHO) ?? null)
        ->setDaloTribunalAction($this->findInArray($this->field['DALO DAHO'], self::DALO_TRIBUNAL_ACTION) ?? null)
        ->setDaloDecisionDate($this->field['Date DALO'] ? new \Datetime($this->field['Date DALO']) : null)
        ->setDomiciliation($this->field['Commune domiciliation'] ? Choices::YES : Choices::NO)
        ->setDomiciliationCity($this->field['Commune domiciliation'])
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function getSiaoRecomendation(): ?int
    {
        $recommendation = $this->field['Préconisation'];

        if (strstr($recommendation, 'Logement droit commun')) {
            return 30;
        }

        foreach (self::RECOMMENDATION_TYPE as $key => $value) {
            if (strstr($recommendation, $key) && 20 === $value) {
                return $value;
            }
        }
        foreach (self::RECOMMENDATION_TYPE as $key => $value) {
            if (strstr($recommendation, $key) && 10 === $value) {
                return $value;
            }
        }

        return null;
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
        return $this->repoPerson->findOneBy([
            'firstname' => $this->person->getFirstname(),
            'lastname' => $this->person->getLastname(),
            'birthdate' => $this->person->getBirthdate(),
        ]);
    }

    protected function createRolePerson(PeopleGroup $peopleGroup): RolePerson
    {
        $rolePerson = (new RolePerson())
                 ->setHead($this->head)
                 ->setRole($this->findInArray($this->field['Rôle'], self::ROLE) ?? 99)
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
                    ->setSupportGroup($supportGroup)
                    ->setPerson($this->person)
                    ->setHead($rolePerson->getHead() ?? false)
                    ->setRole($rolePerson->getRole() ?? 99)
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }

    protected function createHotelSupport(SupportGroup $supportGroup): HotelSupport
    {
        $hotelSupport = (new HotelSupport())
            ->setOriginDept($this->findInArray($this->field['115 origine'], self::DEPARTMENTS) ?? null)
            ->setSsd($this->field['SSD'])
            ->setAgreementDate($this->field['Contrat AMH'] ? (new \Datetime($this->field['Date entrée AMH']))->modify('+7 days') : null)
            ->setEvaluationDate($this->field['Date diagnostic'] ? new \Datetime($this->field['Date diagnostic']) : null)
            ->setAccessId((int) $this->field['ID_ménage'])
            ->setAmhId((int) $this->field['ID_AMH'])
            ->setEndSupportReason($this->findInArray($this->field['Type sortie AMH'], self::END_SUPPORT_REASON) ?? null)
            ->setSupportGroup($supportGroup);

        $this->manager->persist($hotelSupport);

        return $hotelSupport;
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
        if ('Enfant' === $this->field['Rôle']) {
            return null;
        }

        $resourceType = (string) $this->field['Entrée Type ressources'];
        $resourceOther = '';

        if (strstr($resourceType, 'Bourse d\'étude')) {
            $resourceOther = $resourceOther.'Bourses d\'études, ';
        }
        if (strstr($resourceType, 'Indemnités journalières')) {
            $resourceOther = $resourceOther.'Indemnités journalières, ';
        }

        $initEvalPerson = (new InitEvalPerson())
            ->setPaperType($this->findInArray($this->field['Entrée Situation administrative'], self::PAPER_TYPE) ?? null)
            ->setRightSocialSecurity($this->getRightSocialSecurity())
            ->setSocialSecurity($this->getSocialSecurity())
            ->setFamilyBreakdown(99)
            ->setFriendshipBreakdown(99)
            ->setProfStatus($this->findInArray($this->field['Type contrat'], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($this->field['Type contrat'], self::CONTRACT_TYPE) ?? null)
            ->setDebts($this->findInArray($this->field['Dettes'], self::YES_NO) ?? null)
            ->setDebtsAmt((float) $this->field['Montant dettes'])
            ->setResources($this->findInArray($this->field['Entrée Ressources'], self::RESOURCES))
            ->setResourcesAmt((float) $this->field['Entrée Montant ressources'])
            ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
            ->setDisChildAllowance(strstr($resourceType, 'AAEH') ? Choices::YES : 0)
            ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
            ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
            ->setTempWaitingAllowance(strstr($resourceType, 'ATA') ? Choices::YES : 0)
            ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
            ->setFamilyAllowance(strstr($resourceType, 'AF') ? Choices::YES : 0)
            ->setPensionBenefit(strstr($resourceType, 'Pension de retraite') ? Choices::YES : 0)
            ->setSalary(strstr($resourceType, 'Salaire') ? Choices::YES : 0)
            ->setMaintenance(strstr($resourceType, 'Pension alimentaire') ? Choices::YES : 0)
            ->setAsf(strstr($resourceType, 'ASF') ? Choices::YES : 0)
            ->setSolidarityAllowance(strstr($resourceType, 'ASS') ? Choices::YES : 0)
            ->setPaidTraining(strstr($resourceType, 'Formation') ? Choices::YES : 0)
            ->setYouthGuarantee(strstr($resourceType, 'Garantie jeunes') ? Choices::YES : 0)
            ->setDisabilityPension(strstr($resourceType, 'Pension d\'invalidité') ? Choices::YES : 0)
            ->setPaje(strstr($resourceType, 'PAJE') ? Choices::YES : 0)
            ->setActivityBonus(strstr($resourceType, 'Prime d\'activité') ? Choices::YES : 0)
            ->setRessourceOther($resourceOther ? Choices::YES : 0)
            ->setRessourceOtherPrecision($resourceOther)
            ->setSupportPerson($supportPerson);

        $this->manager->persist($initEvalPerson);

        return $initEvalPerson;
    }

    protected function createEvalSocialPerson(EvaluationPerson $evaluationPerson)
    {
        if ('Enfant' != $this->field['Rôle']) {
            $evalSocialPerson = (new EvalSocialPerson())
                ->setViolenceVictim(strstr($this->field['Spécificités'], 'PVV') ? Choices::YES : null)
                ->setDomViolenceVictim(strstr($this->field['Spécificités'], 'FVVC') ? Choices::YES : null)
                ->setHealthProblem(strstr($this->field['Spécificités'], 'Pb santé') ? Choices::YES : null)
                ->setReducedMobility(strstr($this->field['Spécificités'], 'PMR') ? Choices::YES : null)
                ->setChildWelfareBackground(strstr($this->field['Spécificités'], 'PMR') ? Choices::YES : null)
                ->setRightSocialSecurity($this->getRightSocialSecurity())
                ->setSocialSecurity($this->getSocialSecurity())
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalSocialPerson);
        }
    }

    protected function getRightSocialSecurity(): ?int
    {
        $socialSecurity = $this->field['Couverture sociale'];

        foreach (self::RIGHT_SOCIAL_SECURITY as $key => $value) {
            if (strstr($socialSecurity, $key)) {
                return $value;
            }
        }

        return null;
    }

    protected function getSocialSecurity(): ?int
    {
        $socialSecurity = $this->field['Couverture sociale'];

        foreach (self::SOCIAL_SECURITY as $key => $value) {
            if (strstr($socialSecurity, $key)) {
                return $value;
            }
        }

        return null;
    }

    protected function createEvalFamilyPerson(EvaluationPerson $evaluationPerson)
    {
        if ('Enfant' != $this->field['Rôle'] && (!empty($this->field['Situation matrimoniale']))) {
            $evalFamilyPerson = (new EvalFamilyPerson())
            ->setMaritalStatus($this->findInArray($this->field['Situation matrimoniale'], self::MARITAL_STATUS) ?? null)
            ->setEvaluationPerson($evaluationPerson);

            if ('Femme' === $this->field['Sexe']) {
                $evalFamilyPerson->setUnbornChild($this->findInArray($this->field['Enfant à naître'], self::YES_NO));
            }

            $this->manager->persist($evalFamilyPerson);
        }

        if ('Enfant' === $this->field['Rôle']) {
            $evalFamilyPerson = (new EvalFamilyPerson())
                ->setChildToHost($this->findInArray($this->field['Type garde enfant(s)'], self::CHILD_TO_HOST) ?? null)
                ->setChildcareSchoolLocation($this->field['Ecole'])
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalFamilyPerson);
        }
    }

    protected function createEvalAdmPerson(EvaluationPerson $evaluationPerson)
    {
        if (!empty($this->field['Nationalité']) || !empty($this->field['Situation administrative'])) {
            $evalAdmPerson = (new EvalAdmPerson())
            ->setNationality($this->findInArray($this->field['Nationalité'], self::NATIONALITY) ?? null)
            // ->setArrivalDate($this->field['Date arrivée France'] ? new \Datetime($this->field['Date arrivée France']) : null)
            ->setPaper($this->findInArray($this->field['Situation administrative'], self::PAPER) ?? null)
            ->setPaperType($this->findInArray($this->field['Situation administrative'], self::PAPER_TYPE) ?? null)
            // ->setEndValidPermitDate($this->field['Date fin validité titre'] ? new \Datetime($this->field['Date fin validité titre']) : null)
            ->setAsylumBackground($this->findInArray($this->field['Situation administrative'], self::ASYLUM_BACKGROUND) ?? null)
            ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalAdmPerson);
        }
    }

    protected function createEvalProfPerson(EvaluationPerson $evaluationPerson)
    {
        if ((float) $this->field['Age'] >= 16 && !empty($this->field['Type contrat'])) {
            $evalProfPerson = (new EvalProfPerson())
                ->setProfStatus($this->findInArray($this->field['Type contrat'], self::PROF_STATUS) ?? null)
                ->setContractType($this->findInArray($this->field['Type contrat'], self::CONTRACT_TYPE) ?? null)
                ->setWorkPlace($this->field['lieu travail'])

                ->setCommentEvalProf($this->field['Type contrat'])
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalProfPerson);
        }
    }

    protected function createEvalBudgetPerson(EvaluationPerson $evaluationPerson)
    {
        $resourceType = (string) $this->field['Type ressources'];

        if ((float) $this->field['Age'] >= 16 && (!empty($resourceType) || !empty($this->field['Montant ressources']) || !empty($this->field['Dettes']))) {
            $resourceOther = '';

            if (strstr($resourceType, 'Bourse d\'étude')) {
                $resourceOther = $resourceOther.'Bourses d\'études, ';
            }
            if (strstr($resourceType, 'Indemnités journalières')) {
                $resourceOther = $resourceOther.'Indemnités journalières, ';
            }

            $debtType = $this->field['Type dettes'];

            $evalBudgetPerson = (new EvalBudgetPerson())
                ->setCharges((float) $this->field['Montant charges'] > 0 || $this->field['Type charges'] ? Choices::YES : Choices::NO)
                ->setChargesAmt((float) $this->field['Montant charges'])
                ->setDebts($this->findInArray($this->field['Dettes'], self::YES_NO) ?? null)
                ->setDebtsAmt((float) $this->field['Montant dettes'])
                ->setDebtRental(strstr($resourceType, 'Dettes locatives') ? Choices::YES : 0)
                ->setDebtConsrCredit(strstr($resourceType, 'Dette conso') ? Choices::YES : 0)
                ->setDebtFines(strstr($resourceType, 'Amendes') ? Choices::YES : 0)
                ->setDebtTaxDelays(strstr($resourceType, 'Retards impôts') ? Choices::YES : 0)
                ->setDebtRental(strstr($resourceType, 'Dettes locatives') ? Choices::YES : 0)
                ->setDebtOther(strstr($resourceType, 'Autre') ? Choices::YES : 0)
                ->setSettlementPlan('Plan d\'appurement' === $this->field['Démarches endettement'] ? 2 : null)
                ->setMoratorium('Moratoire en cours' === $this->field['Démarches endettement'] ? Choices::YES : null)
                ->setOverIndebtRecord($this->findInArray($this->field['Démarches endettement'], self::OVER_INDEBT_RECORD) ?? null)
                ->setOverIndebtRecordDate($this->field['Date dépôt dossier'] ? new \Datetime($this->field['Date dépôt dossier']) : null)
                ->setResources($this->findInArray($this->field['Ressources'], self::RESOURCES))
                ->setResourcesAmt((float) $this->field['Montant ressources'])
                ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
                ->setDisChildAllowance(strstr($resourceType, 'AAEH') ? Choices::YES : 0)
                ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
                ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
                ->setTempWaitingAllowance(strstr($resourceType, 'ATA') ? Choices::YES : 0)
                ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
                ->setFamilyAllowance(strstr($resourceType, 'AF') ? Choices::YES : 0)
                ->setPensionBenefit(strstr($resourceType, 'Pension de retraite') ? Choices::YES : 0)
                ->setSalary(strstr($resourceType, 'Salaire') ? Choices::YES : 0)
                ->setMaintenance(strstr($resourceType, 'Pension alimentaire') ? Choices::YES : 0)
                ->setAsf(strstr($resourceType, 'ASF') ? Choices::YES : 0)
                ->setSolidarityAllowance(strstr($resourceType, 'ASS') ? Choices::YES : 0)
                ->setPaidTraining(strstr($resourceType, 'Formation') ? Choices::YES : 0)
                ->setYouthGuarantee(strstr($resourceType, 'Garantie jeunes') ? Choices::YES : 0)
                ->setDisabilityPension(strstr($resourceType, 'Pension d\'invalidité') ? Choices::YES : 0)
                ->setPaje(strstr($resourceType, 'PAJE') ? Choices::YES : 0)
                ->setActivityBonus(strstr($resourceType, 'Prime d\'activité') ? Choices::YES : 0)
                ->setRessourceOther($resourceOther ? Choices::YES : 0)
                ->setRessourceOtherPrecision($resourceOther)
                ->setEvaluationPerson($evaluationPerson);

            if (strstr($debtType, 'Santé')
                || strstr($debtType, 'hopital')
                || strstr($debtType, 'Hospitalière')
                || strstr($debtType, 'Hôspitalière')
                || strstr($debtType, 'Hôpital')) {
                $evalBudgetPerson->setDebtHealth(Choices::YES);
            } else {
                $evalBudgetPerson->setDebtHealth(0);
            }

            if (strstr($debtType, 'Cantine')
                || strstr($debtType, 'cantine')) {
                $evalBudgetPerson->setDebtOtherPrecision('Cantine');
            }

            $this->manager->persist($evalBudgetPerson);
        }
    }

    protected function getRole(int $typology)
    {
        $this->gender = 99;
        $this->head = false;
        $this->role = 97;

        if ('Oui' === $this->field['DP']) {
            $this->head = true;
            if (in_array($typology, [1, 4])) {
                $this->gender = Person::GENDER_FEMALE;
            }
            if (in_array($typology, [2, 5])) {
                $this->gender = Person::GENDER_MALE;
            }
            if (in_array($typology, [1, 2])) {
                $this->role = 5;
            } elseif (in_array($typology, [4, 6])) {
                $this->role = 4;
            } elseif (in_array($typology, [3, 6, 7, 8])) {
                $this->role = 1;
            }
        } elseif ('Enfant' === $this->field['Rôle']) {
            $this->role = RolePerson::ROLE_CHILD;
        } elseif ('Concubin(e)' === $this->field['Rôle']) {
            $this->role = 1;
        }
    }

    protected function getStatus(): int
    {
        if ($this->field['Date sortie AMH']) {
            return SupportGroup::STATUS_ENDED;
        }

        if ($this->field['Date entrée AMH'] || $this->field['Date diagnostic']) {
            return SupportGroup::STATUS_IN_PROGRESS;
        }

        return SupportGroup::STATUS_OTHER;
    }

    protected function getStartDate(): ?\DateTime
    {
        if ($this->field['Date entrée AMH']) {
            return new \Datetime($this->field['Date entrée AMH']);
        }

        if ($this->field['Date diagnostic']) {
            return new \Datetime($this->field['Date diagnostic']);
        }

        return null;
    }

    protected function getEndDate(): ?\DateTime
    {
        if ($this->field['Date sortie AMH']) {
            return new \Datetime($this->field['Date sortie AMH']);
        }

        return null;
    }

    protected function createReferent(PeopleGroup $peopleGroup): ?Referent
    {
        $referent = (new Referent())
            ->setName($this->field['Service social référent'])
            ->setType($this->getTypeReferent())
            ->setSocialWorker($this->field['Référent social'])
            ->setComment($this->field['Coordonnées référent'])
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($referent);

        return $referent;
    }

    protected function getTypeReferent()
    {
        $referent = $this->field['Service social référent'];

        if (strstr($referent, 'SSD')
        || strstr($referent, 'antenne')
        || strstr($referent, 'Antenne')
        || strstr($referent, 'Conseil Départmental')) {
            return 6;
        }
        if (strstr($referent, 'CCAS')) {
            return 4;
        }

        return 99;
    }

    protected function getLocalities()
    {
        return [
            'Cergy-Pontoise' => $this->repoSubService->find(1),
            'Pays de France' => $this->repoSubService->find(1),
            'Plaine de France' => $this->repoSubService->find(2),
            'Rives de Seine' => $this->repoSubService->find(3),
            'Vallée de Montmorency' => $this->repoSubService->find(3),
        ];
    }

    protected function createAccommodationGroup(PeopleGroup $peopleGroup, SupportGroup $supportGroup): ?AccommodatioNGroup
    {
        $hotelName = str_replace('HOTEL - ', '', ($this->field['Nom hôtel'] ?? $this->field['Précision lieu hébgt']));

        if (!$hotelName) {
            return null;
        }

        $hotel = $this->getHotel($hotelName);

        $accommodationGroup = (new AccommodationGroup())
            ->setAccommodation($hotel)
            ->setComment(null === $hotel ? $hotelName : null)
            ->setSupportGroup($supportGroup)
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($accommodationGroup);

        return $accommodationGroup;
    }

    protected function getHotels(): array
    {
        $hotels = [];

        foreach ($this->repoAccommodation->findBy(['service' => $this->service]) as $accommodation) {
            $hotels[$accommodation->getName()] = $accommodation;
        }

        return $hotels;
    }

    protected function getHotel(string $hotelName): ?Accommodation
    {
        foreach ($this->hotels as $key => $hotel) {
            if ($hotelName === $key) {
                return $hotel;
            }
        }

        return null;
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
