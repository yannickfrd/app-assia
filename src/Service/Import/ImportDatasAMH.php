<?php

namespace App\Service\Import;

use App\Entity\Device;
use App\Entity\EvalAdmPerson;
use App\Entity\EvalBudgetGroup;
use App\Entity\EvalBudgetPerson;
use App\Entity\EvalFamilyPerson;
use App\Entity\EvalHousingGroup;
use App\Entity\EvalProfPerson;
use App\Entity\EvalSocialPerson;
use App\Entity\EvaluationGroup;
use App\Entity\EvaluationPerson;
use App\Entity\GroupPeople;
use App\Entity\HotelSupport;
use App\Entity\InitEvalGroup;
use App\Entity\InitEvalPerson;
use App\Entity\Note;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Entity\SupportPerson;
use App\Form\Utils\Choices;
use App\Repository\DeviceRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ImportDatasAMH
{
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

    public const SOCIAL_WORKER = [
        'Marie-Laure PEBORDE' => 1,
        'Camille RAVEZ' => 1,
        'Typhaine PECHE' => 1,
        'Cécile BAZIN' => 1,
        'Nathalie POULIQUEN' => 1,
        'Marina DJORDJEVIC' => 1,
        'Melody ROMET' => 1,
        'Marion LE PEZRON' => 1,
        'Gaëlle PRINCET' => 1,
        'Marion FRANCOIS' => 1,
        'Margot COURAUDON' => 1,
        'Marilyse TOURNIER' => 1,
        'Rozenn DOUELE ZAHAR' => 1,
        'Laurine VIALLE' => 1,
        'Ophélie QUENEL' => 1,
        'Camille GALAN' => 1,
        'Christine VESTUR' => 1,
        'Julie MARTIN' => 1,
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
        // 'Oui' => 1,
        // '' => 2,
        // 'SANS' => 2,
    ];

    public const SOCIAL_SECURITY = [
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

    public const DEPARTMENTS = [
        'SIAO 75' => 75,
        'SIAO 77' => 77,
        'SIAO 78' => 78,
        'SIAO 92' => 92,
        'SIAO 93' => 93,
        'SIAO 94' => 94,
        'SIAO 95' => 95,
    ];

    public const RECOMMENDATION = [
    'Hébergement' => 10,
    'Logement Intermédiaire' => 20,
    // 'Sorti d\'hôtel' => 10,
    'Logement' => 30,
    'Logement ' => 30,
    ];

    public const DALO_COMMISSION = [
        'DAHO' => 1,
        'DALO' => 1,
        'NON' => 2,
    ];

    public const DALO_REQUALIFIED_DAHO = [
        'DAHO' => 1,
        'DALO' => 2,
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
        'Prise en charge ASE' => 97,
        'FTM' => 1,
        'Autre' => 97,
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

    public const SECTEUR = [
        'Cergy-Pontoise' => 1,
        'Plaine de France' => 1,
        'Rives de Seine' => 1,
        'Vallée de Montmorency' => 1,
        'Pays de France' => 1,
        'Vexin' => 1,
        'Plaine de France' => 1,
    ];

    protected $user;
    protected $manager;
    protected $repoPerson;

    protected $datas;
    protected $row;

    protected $device;
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

    public function __construct(Security $security, EntityManagerInterface $manager, DeviceRepository $repoDevice, PersonRepository $repoPerson)
    {
        $this->user = $security->getUser();
        $this->manager = $manager;
        $this->device = $repoDevice->find(16); // Opération ciblée
        $this->repoPerson = $repoPerson;
    }

    public function getDatas(string $fileName)
    {
        $this->datas = [];

        $row = 1;
        if (($handle = fopen($fileName, 'r')) !== false) {
            while (($data = fgetcsv($handle, 2000, ';')) !== false) {
                $num = count($data);
                ++$row;
                $row = [];
                for ($col = 0; $col < $num; ++$col) {
                    $cel = iconv('CP1252', 'UTF-8', $data[$col]);
                    $date = \DateTime::createFromFormat('d/m/Y', $cel, new \DateTimeZone(('UTC')));
                    if ($date) {
                        $cel = $date->format('Y-m-d');
                    }
                    if (isset($this->datas[0])) {
                        if (isset($this->datas[0][$col])) {
                            $row[$this->datas[0][$col]] = $cel;
                        }
                    } else {
                        $row[] = $cel;
                    }
                }
                $this->datas[] = $row;
            }
            fclose($handle);
        }

        return $this->datas;
    }

    public function importInDatabase(string $fileName, Service $service): array
    {
        $this->fields = $this->getDatas($fileName);

        $i = 0;
        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $typology = $this->findInArray($this->field['Compo'], self::FAMILY_TYPOLOGY) ?? 9;

                $this->getRole($typology);
                $this->person = $this->getPerson();
                $this->personExists = $this->personExistsInDatabase($this->person);

                $this->checkGroupExists($typology, $service, $this->device);

                $this->person = $this->createPerson($this->items[$this->field['ID_ménage']]['groupPeople']);

                $support = $this->items[$this->field['ID_ménage']]['supports'][$this->field['ID_AMH']];
                $supportGroup = $support['support'];
                $evaluationGroup = $support['evaluation'];

                $supportPerson = $this->createSupportPerson($supportGroup);
                $this->createEvaluationPerson($evaluationGroup, $supportPerson);
            }
            ++$i;
        }

        // dump($this->existPeople);
        // dump($this->duplicatedPeople);
        dd($this->items);
        $this->manager->flush();

        return $this->items;
    }

    protected function getPerson()
    {
        return (new Person())
                ->setLastname($this->field['Nom'])
                ->setFirstname($this->field['Prénom'])
                ->setBirthdate($this->field['Date naissance'] ? new \Datetime($this->field['Date naissance']) : null)
                ->setGender($this->findInArray($this->field['Sexe'], self::GENDER) ?? null)
                ->setCreatedBy($this->user)
                ->setUpdatedBy($this->user);
    }

    protected function checkGroupExists(int $typology, Service $service, Device $device)
    {
        // Si le groupe n'existe pas encore, on le crée ainsi que le suivi et l'évaluation sociale.
        if (false == $this->groupExists($service, $device)) {
            // Si la personne existe déjà dans la base de données, on récupère son groupe.
            if ($this->personExists) {
                $groupPeople = $this->personExists->getRolesPerson()->first()->getGroupPeople();
            // Sinon, on crée le groupe.
            } else {
                $groupPeople = $this->createGroupPeople($typology);
            }

            $supportGroup = $this->createSupportGroup($groupPeople, $service, $device);
            $evaluationGroup = $this->createEvaluationGroup($supportGroup);

            // On ajoute le groupe et le suivi dans le tableau associatif.
            $this->items[$this->field['ID_ménage']] = [
                'groupPeople' => $groupPeople,
                'supports' => [
                    $this->field['ID_AMH'] => [
                        'support' => $supportGroup,
                        'evaluation' => $evaluationGroup,
                    ],
                ],
            ];
        }
    }

    protected function groupExists(Service $service, Device $device)
    {
        $groupExists = false;
        // Vérifie si le groupe de la personne existe déjà.
        foreach ($this->items as $key => $value) {
            // Si déjà créé, on vérifie le suivi social.
            if ($key == $this->field['ID_ménage']) {
                $groupExists = true;

                $supports = $this->items[$this->field['ID_ménage']]['supports'];

                $supportExists = false;
                // Vérifie si le suivi du groupe de la personne a déjà été créé.
                foreach ($supports as $key => $value) {
                    if ($key == $this->field['ID_AMH']) {
                        $supportExists = true;
                    }
                }

                // Si le suivi social du groupe n'existe pas encore, on le crée ainsi que l'évaluation sociale.
                if (false == $supportExists) {
                    $supportGroup = $this->createSupportGroup($this->items[$this->field['ID_ménage']]['groupPeople'], $service, $device);
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

    protected function createGroupPeople(int $typology): GroupPeople
    {
        if ($this->field['Rôle'] == 'CHEF DE FAMILLE') {
            $this->personExistsInDatabase();
        }

        $groupPeople = (new GroupPeople())
                    ->setFamilyTypology($typology)
                    ->setNbPeople((int) $this->field['Nb personnes'])
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($groupPeople);

        return $groupPeople;
    }

    protected function createSupportGroup(GroupPeople $groupPeople, Service $service, Device $device): SupportGroup
    {
        $supportGroup = (new SupportGroup())
                    ->setStatus($this->getStatus($this->field))
                    ->setStartDate($this->getStartDate($this->field))
                    ->setEndDate($this->getEndDate($this->field))
                    ->setEndStatus(null)
                    ->setEndStatusComment($this->field['Type sortie AMH'])
                    ->setNbPeople((int) $this->field['Nb personnes'])
                    ->setGroupPeople($groupPeople)
                    ->setService($service)
                    ->setDevice($device)
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($supportGroup);

        if ($this->field['Date diagnostic']) {
            $this->createHotelSupport($supportGroup);
        }

        return $supportGroup;
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

    protected function createEvalBudgetGroup(EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setEvaluationGroup($evaluationGroup)
            ->setResourcesGroupAmt((float) $this->field['Montant ressources'])
            ->setChargesGroupAmt((float) $this->field['Montant charges'])
            ->setDebtsGroupAmt((float) $this->field['Montant dettes']);
        // ->setBudgetBalanceAmt((float) ($this->field['Montant ressources'] - $this->field['Montant charges']));

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
        // ->setSiaoRequestDept($this->findInArray($this->field['SIAO prescripteur'], self::DEPARTMENTS) ?? null)
        // ->setSiaoRecommendation($this->findInArray($this->field['Préconisation'], self::RECOMMENDATION) ?? null)
        ->setSocialHousingRequest($this->findInArray($this->field['DLS'], self::YES_NO) ?? null)
        // ->setSocialHousingRequestId($this->field['NUR'])
        // ->setDaloCommission($this->findInArray($this->field['DALO / DAHO'], self::DALO_COMMISSION) ?? null)
        // ->setDaloRequalifiedDaho($this->findInArray($this->field['DALO / DAHO'], self::DALO_REQUALIFIED_DAHO) ?? null)
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function createPerson(GroupPeople $groupPeople): Person
    {
        $duplicatedPerson = false;

        if ($this->personExists) {
            $this->person = $this->personExists;
            $this->existPeople[] = $this->person;
        } else {
            foreach ($this->people as $person2) {
                if ($this->person->getLastname() == $person2->getLastname()
                        && $this->person->getFirstname() == $person2->getFirstname()
                        && $this->person->getBirthdate() == $person2->getBirthdate()) {
                    $this->duplicatedPeople[] = $this->person;
                    $duplicatedPerson = true;
                    $this->person = $person2;
                }
            }
            if (false == $duplicatedPerson) {
                $this->manager->persist($this->person);
                $this->person->addRolesPerson($this->createRolePerson($groupPeople));
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

    protected function createRolePerson(GroupPeople $groupPeople): RolePerson
    {
        $rolePerson = (new RolePerson())
                 ->setHead($this->head)
                 ->setRole($this->findInArray($this->field['Rôle'], self::ROLE) ?? null)
                 ->setPerson($this->person)
                 ->setGroupPeople($groupPeople);

        $this->manager->persist($rolePerson);

        return $rolePerson;
    }

    protected function createSupportPerson(SupportGroup $supportGroup): SupportPerson
    {
        $rolePerson = $this->person->getRolesPerson()->first();

        $supportPerson = (new SupportPerson())
                    ->setStatus($this->getStatus($this->field))
                    ->setStartDate($this->getStartDate($this->field))
                    ->setEndDate($this->getEndDate($this->field))
                    ->setSupportGroup($supportGroup)
                    ->setPerson($this->person)
                    ->setHead($rolePerson->getHead())
                    ->setRole($rolePerson->getRole())
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }

    protected function createHotelSupport(SupportGroup $supportGroup): HotelSupport
    {
        $hotelSupport = (new HotelSupport())
            ->setEvaluationDate($this->field['Date diagnostic'] ? new \Datetime($this->field['Date diagnostic']) : null)
            // ->setRecommendation($this->findInArray($this->field['Préconisation'], self::RECOMMENDATION) ?? null)
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
        if ($this->field['Rôle'] == 'Enfant') {
            return null;
        }
        $resourceType = $this->field['Type ressources'];
        $resourceOther = null;

        $initEvalPerson = (new InitEvalPerson())
            ->setPaperType($this->findInArray($this->field['Situation administrative'], self::PAPER_TYPE) ?? null)
            ->setRightSocialSecurity($this->findInArray($this->field['Couverture sociale'], self::RIGHT_SOCIAL_SECURITY) ?? null)
            ->setSocialSecurity($this->findInArray($this->field['Couverture sociale'], self::SOCIAL_SECURITY) ?? null)
            ->setProfStatus($this->findInArray($this->field['Type contrat'], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($this->field['Type contrat'], self::CONTRACT_TYPE) ?? null)
            ->setResources($this->findInArray($resourceType, self::RESOURCES) ?? null)
            ->setResourcesAmt((float) $this->field['Montant ressources'])
            ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
            ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
            ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
            ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
            ->setFamilyAllowance(strstr($resourceType, 'PF') ? Choices::YES : 0)
            ->setSalary(strstr($resourceType, 'SALAIRE') ? Choices::YES : 0)
            ->setMaintenance(strstr($resourceType, 'PENSION ALIMENTAIRE') ? Choices::YES : 0)
            ->setRessourceOther($resourceOther ? Choices::YES : 0)
            ->setRessourceOtherPrecision($resourceOther)
            ->setDebts($this->findInArray($this->field['Dettes'], self::YES_NO) ?? null)
            ->setDebtsAmt((float) $this->field['Montant dettes'])
            ->setSupportPerson($supportPerson);

        $this->manager->persist($initEvalPerson);

        return $initEvalPerson;
    }

    protected function createEvalSocialPerson(EvaluationPerson $evaluationPerson)
    {
        if ($this->field['Rôle'] != 'Enfant') {
            $evalSocialPerson = (new EvalSocialPerson())
                ->setRightSocialSecurity($this->findInArray($this->field['Couverture sociale'], self::RIGHT_SOCIAL_SECURITY) ?? null)
                ->setSocialSecurity($this->findInArray($this->field['Couverture sociale'], self::SOCIAL_SECURITY) ?? null)
                // ->setEndRightsSocialSecurityDate($this->field['Date fin validité Sécurité sociale'] ? new \Datetime($this->field['Date fin validité Sécurité sociale']) : null)
                // ->setCommentEvalSocialPerson($this->field['Suivi social'] ? 'Suivi social : Oui' : null)
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalSocialPerson);
        }
    }

    protected function createEvalFamilyPerson(EvaluationPerson $evaluationPerson)
    {
        if ($this->field['Rôle'] != 'Enfant' && (!empty($this->field['Situation matrimoniale']))) {
            $evalFamilyPerson = (new EvalFamilyPerson())
                ->setMaritalStatus($this->findInArray($this->field['Situation matrimoniale'], self::MARITAL_STATUS) ?? null)
                // ->setUnbornChild($this->findInArray($this->field['Enfant à naître'], self::YES_NO) ?? null)
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
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalProfPerson);
        }
    }

    protected function createEvalBudgetPerson(EvaluationPerson $evaluationPerson)
    {
        $resourceType = $this->field['Type ressources'];

        if ((float) $this->field['Age'] >= 16 && (!empty($resourceType) || !empty($this->field['Montant ressources']) || !empty($this->field['Dettes']))) {
            $resourceOther = null;

            $evalBudgetPerson = (new EvalBudgetPerson())
                ->setCharges((float) $this->field['Montant charges'] > 0 ? Choices::YES : Choices::NO)
                ->setChargesAmt((float) $this->field['Montant charges'])
                ->setDebts($this->findInArray($this->field['Dettes'], self::YES_NO) ?? null)
                // ->setIncomeTax($this->findInArray($this->field['AVIS D\'IMPOSITION'], self::YES_NO) ?? null)
                ->setDebtsAmt((float) $this->field['Montant dettes'])
                // ->setOverIndebtRecord($this->findInArray($this->field['Plan apurement'], self::OVER_INDEBT_RECORD) ?? null)
                // ->setSettlementPlan($this->findInArray($this->field['Plan apurement'], self::SETTLEMENT_PLAN) ?? null)
                ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
                ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
                ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
                ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
                ->setFamilyAllowance(strstr($resourceType, 'PF') ? Choices::YES : 0)
                ->setSalary(strstr($resourceType, 'Salaire') ? Choices::YES : 0)
                ->setMaintenance(strstr($resourceType, 'Pension alimentaire') ? Choices::YES : 0)
                ->setRessourceOther($resourceOther ? Choices::YES : 0)
                ->setRessourceOtherPrecision($resourceOther)
                ->setResources($this->findInArray($resourceType, self::RESOURCES) ?? null)
                ->setResourcesAmt((float) $this->field['Montant ressources'])
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalBudgetPerson);
        }
    }

    protected function findInArray($needle, array $haystack): ?int
    {
        foreach ($haystack as $key => $value) {
            if ($key == $needle) {
                return $value;
            }
        }

        return false;
    }

    protected function getRole(int $typology)
    {
        $this->gender = 99;
        $this->head = false;
        $this->role = 97;

        if ($this->field['Rôle'] == 'CHEF DE FAMILLE') {
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
        } elseif ($this->field['Rôle'] == 'Enfant') {
            $this->role = RolePerson::ROLE_CHILD;
        } elseif ($this->field['Rôle'] == 'CONJOINT( E )') {
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
            return new \Datetime($this->field['Date sortie AMH']);
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

    protected function createNote(SupportGroup $supportGroup, string $title, string $content): Note
    {
        $note = (new Note())
        ->setTitle($title)
        ->setContent($content)
        ->setSupportGroup($supportGroup)
        ->setCreatedBy($this->user)
        ->setUpdatedBy($this->user);

        $this->manager->persist($note);

        return $note;
    }
}
