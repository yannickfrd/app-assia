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

class ImportDatasOC
{
    public const YES_NO = [
        'OUI' => 1,
        'NON' => 2,
        'EN COURS' => 3,
        'NR' => 99,
    ];

    public const YES_NO_BOOLEAN = [
        'NON' => 0,
        'OUI' => 1,
    ];

    public const SOCIAL_WORKER = [
        'LAURIE P' => 1,
        'MYLENA E' => 1,
        'FLORIANE B' => 1,
        'MICHAEL O' => 1,
        'ELLEN H' => 1,
        'ROZENN D-Z' => 1,
        'MARILYSE T' => 1,
    ];

    public const HEAD = [
        'CHEF DE FAMILLE' => true,
        'CONJOINT( E )' => false,
        'CONJOINTE' => false,
        'ENFANT' => false,
        'MEMBRE DE LA FAMILLE' => false,
        '' => false,
    ];

    public const GENDER = [
        'F' => Person::GENDER_FEMALE, // A Vérifier
        'H' => Person::GENDER_MALE, // A Vérifier
        '' => 99,
    ];

    public const ROLE = [
        'CHEF DE FAMILLE' => 1,
        'CONJOINT( E )' => 1,
        'CONJOINTE' => 1,
        'ENFANT' => 3,
        'MEMBRE DE LA FAMILLE' => 6,
        '' => 99,
    ];

    public const FAMILY_TYPOLOGY = [
        'FS' => 1,
        'HS' => 2,
        'C' => 3,
        'C+1' => 6,
        'C+2' => 6,
        'C+3' => 6,
        'C+4' => 6,
        'C+5' => 6,
        'C+6' => 6,
        'F+1' => 4,
        'F+2' => 4,
        'F+3' => 4,
        'F+4' => 4,
        'F+5' => 4,
        'F+6' => 4,
        'H+1' => 5,
        'H+2' => 5,
        'GRPE ADULTE AVEC ENF' => 8,
        'GRPE ADULTE SS ENF' => 7,
        '' => 9,
        ];

    public const NB_PEOPLE = [
        'FS' => 1,
        'HS' => 1,
        'C' => 2,
        'C+1' => 3,
        'C+2' => 4,
        'C+3' => 5,
        'C+4' => 6,
        'C+5' => 7,
        'C+6' => 8,
        'F+1' => 2,
        'F+2' => 3,
        'F+3' => 4,
        'F+4' => 5,
        'F+5' => 6,
        'F+6' => 7,
        'H+1' => 2,
        'H+2' => 3,
        'GRPE ADULTE AVEC ENF' => 1,
        'GRPE ADULTE SS ENF' => 1,
        '' => 1,
    ];

    public const MARITAL_STATUS = [
        'CELIBATAIRE' => 1,
        'DIVORCE( E )' => 3,
        'MARIE( E )' => 4,
        'SEPARE( E )' => 6,
        'CONCUBIN (E )' => 2,
        'VEUF (VE)' => 7,
        'PACSE ( E )' => 5,
    ];

    public const NATIONALITY = [
        'FRANCAISE' => 1,
        'HORS UE' => 3,
        'UE' => 2,
    ];

    public const PAPER = [
        'CARTE NATIONALE D\'IDENTITE' => 1,
        'CARTE DE SEJOUR' => 1,
        'DEMARCHES DE REGULARISATION EN COURS' => 3,
        'DEMANDE D\'ASILE EN COURS' => null,
        'EN SITUATION IRREGULIERE' => 2,
        'NON RENSEIGNEE' => 99,
        'EUROPEEN' => 1,
        'ENFANT - REFUGIE' => 1,
    ];

    public const PAPER_TYPE = [
        'CARTE NATIONALE D\'IDENTITE' => 01,
        'CARTE DE SEJOUR' => 21,
        'DEMARCHES DE REGULARISATION EN COURS' => 1,
        'DEMANDE D\'ASILE EN COURS' => null,
        'EN SITUATION IRREGULIERE' => null,
        'NON RENSEIGNEE' => 99,
        'EUROPEEN' => 97,
        'ENFANT - REFUGIE' => 97,
    ];

    public const ASYLUM_BACKGROUND = [
        'CARTE NATIONALE D\'IDENTITE' => null,
        'CARTE DE SEJOUR' => null,
        'DEMARCHES DE REGULARISATION EN COURS' => null,
        'DEMANDE D\'ASILE EN COURS' => 1,
        'EN SITUATION IRREGULIERE' => null,
        'NON RENSEIGNEE' => null,
        'EUROPEEN' => null,
        'ENFANT - REFUGIE' => 1,
    ];

    public const RIGHT_TO_RESIDE = [
        'CARTE NATIONALE D\'IDENTITE' => null,
        'CARTE DE SEJOUR' => null,
        'DEMARCHES DE REGULARISATION EN COURS' => null,
        'DEMANDE D\'ASILE EN COURS' => 2,
        'EN SITUATION IRREGULIERE' => null,
        'NON RENSEIGNEE' => null,
        'EUROPEEN' => null,
        'ENFANT - REFUGIE' => 4,
    ];

    public const RIGHT_SOCIAL_SECURITY = [
        'AME' => 1,
        'CMU' => 1,
        'REGIME GENERALE' => 1,
        'EN COURS D\'OUVERTURE' => 3,
        'EN COURS DE RENOUVELLEMENT' => 3,
        'SANS' => 2,
    ];

    public const SOCIAL_SECURITY = [
        'AME' => 5,
        'CMU' => 3,
        'REGIME GENERALE' => 1,
        'EN COURS D\'OUVERTURE' => null,
        'EN COURS DE RENOUVELLEMENT' => null,
        'SANS' => null,
    ];

    public const PROF_STATUS = [
        'EN EMPLOI - CDI' => 8,
        'EN EMPLOI - CDD' => 8,
        'EN EMPLOI - INTERIM' => 8,
        'EN EMPLOI - CONTRAT AIDE' => 8,
        'EN RECHERCHE D\'EMPLOI' => 2,
        'EN FORMATION' => 3,
        'ETUDIANT' => 5,
        'RETRAITE' => 7,
        'SANS EMPLOI' => 9,
        'AUTO ENTREPRENEUR' => 1,
    ];

    public const CONTRACT_TYPE = [
        'EN EMPLOI - CDI' => 2,
        'EN EMPLOI - CDD' => 1,
        'EN EMPLOI - INTERIM' => 7,
        'EN EMPLOI - CONTRAT AIDE' => 3,
        'EN RECHERCHE D\'EMPLOI' => null,
        'EN FORMATION' => null,
        'ETUDIANT' => null,
        'RETRAITE' => null,
        'SANS EMPLOI' => null,
        'AUTO ENTREPRENEUR' => null,
    ];

    public const RESOURCES = [
        'AAH' => 1,
        'ADA' => 1,
        'AIDE EXTERIEURE' => 1,
        'ARE' => 1,
        'PENSION ALIMENTAIRE' => 1,
        'PF' => 1,
        'RESSOURCES NON DECLAREES' => 1,
        'RSA' => 1,
        'RSA + PF' => 1,
        'SALAIRE' => 1,
        'SALAIRE + PF' => 1,
        'SALAIRE + RSA' => 1,
        'SANS RESSOURCE' => 2,
        '' => 99,
    ];

    public const INCOME_TAX = [
        'OUI' => 1,
        'NON' => 2,
        'A VERIFIER' => 99,
    ];

    public const DEBTS = [
        'OUI' => 1,
        'NON' => 2,
    ];

    public const OVER_INDEBT_RECORD = [
        'OUI' => 1,
        'NON' => null,
    ];

    public const SETTLEMENT_PLAN = [
        'OUI' => 2,
        'NON' => null,
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

    public const END_STATUS = [
        // 'APEC' => 1,
        // 'Hébergement' => 1,
        // 'Logement droit commun' => 301,
        // 'Logement intermédiaire' => 1,
        // 'Logement privé' => 1,
        // 'Solution personnelle' => 1,
    ];

    protected $user;
    protected $manager;
    protected $repoPerson;

    protected $datas;
    protected $row;

    protected $device;
    protected $person;
    protected $personExists;

    protected $groups = [];
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
        $this->device = $repoDevice->find(17); // Opération ciblée
        $this->repoPerson = $repoPerson;
    }

    public function getDatas(string $fileName)
    {
        $this->datas = [];

        $row = 1;
        if (($handle = fopen($fileName, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ';')) !== false) {
                $num = count($data);
                ++$row;
                $row = [];
                for ($col = 0; $col < $num; ++$col) {
                    $cel = iconv('CP1252', 'UTF-8', $data[$col]);
                    $date = \DateTime::createFromFormat('d/m/Y', $cel, new \DateTimeZone(('UTC')));
                    if ($date) {
                        $cel = $date->format('Y-m-d');
                    }
                    isset($this->datas[0]) ? $row[$this->datas[0][$col]] = $cel : $row[] = $cel;
                }
                $this->datas[] = $row;
            }
            fclose($handle);
        }

        return $this->datas;
    }

    public function importInDatabase(string $fileName, Service $service)
    {
        $this->datas = $this->getDatas($fileName);

        $i = 0;

        foreach ($this->datas as $row) {
            $this->row = $row;
            if ($i > 0) {
                $typology = $this->findInArray($this->row['Compo'], self::FAMILY_TYPOLOGY) ?? 9;

                $this->getRole($typology);
                $this->person = $this->getPerson();
                $this->personExists = $this->personExistsInDatabase($this->person);

                $this->checkGroupExists($typology, $service, $this->device);

                $this->person = $this->createPerson($this->groups[$this->row['ID_GIP']]['groupPeople']);

                $support = $this->groups[$this->row['ID_GIP']]['supports'][$this->row['ID_Support']];
                $supportGroup = $support['support'];
                $evaluationGroup = $support['evaluation'];

                $supportPerson = $this->createSupportPerson($supportGroup);
                $this->createEvaluationPerson($evaluationGroup, $supportPerson);
            }
            ++$i;
        }

        // dump($this->existPeople);
        // dump($this->duplicatedPeople);
        // dd($this->groups);
        $this->manager->flush();
    }

    protected function getPerson()
    {
        return (new Person())
                ->setLastname($this->row['Nom'])
                ->setFirstname($this->row['Prénom'])
                ->setBirthdate($this->row['Date naissance'] ? new \Datetime($this->row['Date naissance']) : null)
                ->setGender($this->gender)
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
            $this->groups[$this->row['ID_GIP']] = [
                'groupPeople' => $groupPeople,
                'supports' => [
                    $this->row['ID_Support'] => [
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
        foreach ($this->groups as $key => $value) {
            // Si déjà créé, on vérifie le suivi social.
            if ($key == $this->row['ID_GIP']) {
                $groupExists = true;

                $supports = $this->groups[$this->row['ID_GIP']]['supports'];

                $supportExists = false;
                // Vérifie si le suivi du groupe de la personne a déjà été créé.
                foreach ($supports as $key => $value) {
                    if ($key == $this->row['ID_Support']) {
                        $supportExists = true;
                    }
                }

                // Si le suivi social du groupe n'existe pas encore, on le crée ainsi que l'évaluation sociale.
                if (false == $supportExists) {
                    $supportGroup = $this->createSupportGroup($this->groups[$this->row['ID_GIP']]['groupPeople'], $service, $device);
                    $evaluationGroup = $this->createEvaluationGroup($supportGroup);

                    $this->groups[$this->row['ID_GIP']]['supports'][$this->row['ID_Support']] = [
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
        if ($this->row['Rôle'] == 'CHEF DE FAMILLE') {
            $this->personExistsInDatabase();
        }

        $groupPeople = (new GroupPeople())
                    ->setFamilyTypology($typology)
                    ->setNbPeople($this->findInArray($this->row['Compo'], self::NB_PEOPLE) ?? null)
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($groupPeople);

        return $groupPeople;
    }

    protected function createSupportGroup(GroupPeople $groupPeople, Service $service, Device $device): SupportGroup
    {
        $supportGroup = (new SupportGroup())
                    ->setStatus($this->getStatus($this->row))
                    ->setStartDate($this->getStartDate($this->row))
                    ->setEndDate($this->getEndDate($this->row))
                    ->setEndStatus(null)
                    ->setEndStatusComment($this->row['Motif sortie'])
                    ->setNbPeople($this->findInArray($this->row['Compo'], self::NB_PEOPLE) ?? null)
                    ->setGroupPeople($groupPeople)
                    ->setService($service)
                    ->setDevice($device)
                    ->setCreatedBy($this->user)
                    ->setUpdatedBy($this->user);

        $this->manager->persist($supportGroup);

        if ($this->row['Date diagnostic']) {
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
            ->setSiaoRequest($this->findInArray($this->row['Demande SIAO préalable au diag'], self::YES_NO) ?? null)
            ->setSocialHousingRequest($this->findInArray($this->row['Demande de logement social active'], self::DLS) ?? null)
            ->setResourcesGroupAmt((float) $this->row['Montant ressources'])
            ->setDebtsGroupAmt((float) $this->row['Montant dettes'])
            ->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        return $initEvalGroup;
    }

    protected function createEvalBudgetGroup(EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setEvaluationGroup($evaluationGroup)
            ->setResourcesGroupAmt((float) $this->row['Montant ressources'])
            ->setChargesGroupAmt((float) $this->row['Montant charges'])
            ->setDebtsGroupAmt((float) $this->row['Montant dettes']);
        // ->setBudgetBalanceAmt((float) ($this->row['Montant ressources'] - $this->row['Montant charges']));

        $this->manager->persist($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(EvaluationGroup $evaluationGroup): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
        ->setHousingStatus(100)
        ->setSiaoRequest($this->row['Date demande initiale SIAO'] ? Choices::YES : Choices::NO)
        ->setSiaoRequestDate($this->row['Date demande initiale SIAO'] ? new \Datetime($this->row['Date demande initiale SIAO']) : null)
        ->setSiaoUpdatedRequestDate($this->row['Date dernière actualisation SIAO'] ? new \Datetime($this->row['Date dernière actualisation SIAO']) : null)
        ->setSiaoRequestDept($this->findInArray($this->row['SIAO prescripteur'], self::DEPARTMENTS) ?? null)
        ->setSiaoRecommendation($this->findInArray($this->row['Préconisation'], self::RECOMMENDATION) ?? null)
        ->setSocialHousingRequest($this->findInArray($this->row['Demande de logement social active'], self::DLS) ?? (!empty($this->row['NUR']) ? Choices::YES : Choices::NO))
        ->setSocialHousingRequestId($this->row['NUR'])
        ->setDaloCommission($this->findInArray($this->row['DALO / DAHO'], self::DALO_COMMISSION) ?? null)
        ->setDaloRequalifiedDaho($this->findInArray($this->row['DALO / DAHO'], self::DALO_REQUALIFIED_DAHO) ?? null)
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
                 ->setRole($this->role)
                 ->setPerson($this->person)
                 ->setGroupPeople($groupPeople);

        $this->manager->persist($rolePerson);

        return $rolePerson;
    }

    protected function createSupportPerson(SupportGroup $supportGroup): SupportPerson
    {
        $rolePerson = $this->person->getRolesPerson()->first();

        $supportPerson = (new SupportPerson())
                    ->setStatus($this->getStatus($this->row))
                    ->setStartDate($this->getStartDate($this->row))
                    ->setEndDate($this->getEndDate($this->row))
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
            ->setGipId($this->row['ID_GIP'])
            ->setEvaluationDate($this->row['Date diagnostic'] ? new \Datetime($this->row['Date diagnostic']) : null)
            ->setDiagComment($this->row['TS diagnostic'] ? 'TS : '.$this->row['TS diagnostic'] : null)
            // ->setSupportStartDate($this->getStartDate($this->row))
            ->setDepartmentAnchor(['Ancrage 95'] == 'OUI' ? 95 : null)
            ->setRecommendation($this->findInArray($this->row['Préconisation'], self::RECOMMENDATION) ?? null)
            ->setSupportEndDate($this->getEndDate($this->row))
            ->setSupportComment($this->row['TS accompagnement'] ? 'TS : '.$this->row['TS accompagnement'] : null)
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
        if ($this->row['Rôle'] == 'ENFANT') {
            return null;
        }

        $resourceType = $this->row['Type ressources'];
        $resourceOther = null;

        if ($resourceType == 'AIDE EXTERIEURE') {
            $resourceOther = 'Aide extérieure';
        }
        if ($resourceType == 'RESSOURCES NON DECLAREES') {
            $resourceOther = 'Ressources non déclarées';
        }

        $initEvalPerson = (new InitEvalPerson())
            ->setPaperType($this->findInArray($this->row['Situation administrative'], self::PAPER_TYPE) ?? null)
            ->setRightSocialSecurity($this->findInArray($this->row['Couverture maladie'], self::RIGHT_SOCIAL_SECURITY) ?? null)
            ->setSocialSecurity($this->findInArray($this->row['Couverture maladie'], self::SOCIAL_SECURITY) ?? null)
            ->setProfStatus($this->findInArray($this->row['Emploi'], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($this->row['Emploi'], self::CONTRACT_TYPE) ?? null)
            ->setResources($this->findInArray($resourceType, self::RESOURCES) ?? null)
            ->setResourcesAmt((float) $this->row['Montant ressources'])
            ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
            ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
            ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
            ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
            ->setFamilyAllowance(strstr($resourceType, 'PF') ? Choices::YES : 0)
            ->setSalary(strstr($resourceType, 'SALAIRE') ? Choices::YES : 0)
            ->setMaintenance(strstr($resourceType, 'PENSION ALIMENTAIRE') ? Choices::YES : 0)
            ->setRessourceOther($resourceOther ? Choices::YES : 0)
            ->setRessourceOtherPrecision($resourceOther)
            ->setDebts($this->findInArray($this->row['Dettes'], self::DEBTS) ?? null)
            ->setDebtsAmt((float) $this->row['Montant dettes'])
            ->setSupportPerson($supportPerson);

        $this->manager->persist($initEvalPerson);

        return $initEvalPerson;
    }

    protected function createEvalSocialPerson(EvaluationPerson $evaluationPerson)
    {
        if ($this->row['Rôle'] != 'ENFANT') {
            $evalSocialPerson = (new EvalSocialPerson())
                ->setRightSocialSecurity($this->findInArray($this->row['Couverture maladie'], self::RIGHT_SOCIAL_SECURITY) ?? null)
                ->setSocialSecurity($this->findInArray($this->row['Couverture maladie'], self::SOCIAL_SECURITY) ?? null)
                ->setEndRightsSocialSecurityDate($this->row['Date fin validité Sécurité sociale'] ? new \Datetime($this->row['Date fin validité Sécurité sociale']) : null)
                ->setCommentEvalSocialPerson($this->row['Suivi social'] ? 'Suivi social : Oui' : null)
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalSocialPerson);
        }
    }

    protected function createEvalFamilyPerson(EvaluationPerson $evaluationPerson)
    {
        if ($this->row['Rôle'] != 'ENFANT' && (!empty($this->row['Grossesse']) || !empty($this->row['Situation matrimoniale']))) {
            $evalFamilyPerson = (new EvalFamilyPerson())
                ->setMaritalStatus($this->findInArray($this->row['Situation matrimoniale'], self::MARITAL_STATUS) ?? null)
                ->setUnbornChild($this->findInArray($this->row['Grossesse'], self::YES_NO) ?? null)
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalFamilyPerson);
        }
    }

    protected function createEvalAdmPerson(EvaluationPerson $evaluationPerson)
    {
        if (!empty($this->row['Nationalité']) || !empty($this->row['Situation administrative'])) {
            $evalAdmPerson = (new EvalAdmPerson())
            ->setNationality($this->findInArray($this->row['Nationalité'], self::NATIONALITY) ?? null)
            ->setArrivalDate($this->row['Date arrivée France'] ? new \Datetime($this->row['Date arrivée France']) : null)
            ->setPaper($this->findInArray($this->row['Situation administrative'], self::PAPER) ?? null)
            ->setPaperType($this->findInArray($this->row['Situation administrative'], self::PAPER_TYPE) ?? null)
            ->setEndValidPermitDate($this->row['Date fin validité titre'] ? new \Datetime($this->row['Date fin validité titre']) : null)
            ->setAsylumBackground($this->findInArray($this->row['Situation administrative'], self::ASYLUM_BACKGROUND) ?? null)
            ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalAdmPerson);
        }
    }

    protected function createEvalProfPerson(EvaluationPerson $evaluationPerson)
    {
        if ((float) $this->row['Age'] >= 16 && !empty($this->row['Emploi'])) {
            $evalProfPerson = (new EvalProfPerson())
                ->setProfStatus($this->findInArray($this->row['Emploi'], self::PROF_STATUS) ?? null)
                ->setContractType($this->findInArray($this->row['Emploi'], self::CONTRACT_TYPE) ?? null)
                ->setEvaluationPerson($evaluationPerson);

            $this->manager->persist($evalProfPerson);
        }
    }

    protected function createEvalBudgetPerson(EvaluationPerson $evaluationPerson)
    {
        $resourceType = $this->row['Type ressources'];

        if ((float) $this->row['Age'] >= 16 && (!empty($resourceType) || !empty($this->row['Montant ressources']) || !empty($this->row['Dettes']))) {
            $resourceOther = null;
            if ($resourceType == 'AIDE EXTERIEURE') {
                $resourceOther = 'Aide extérieure';
            } elseif ($resourceType == 'RESSOURCES NON DECLAREES') {
                $resourceOther = 'Ressources non déclarées';
            }

            $evalBudgetPerson = (new EvalBudgetPerson())
                ->setCharges((float) $this->row['Montant charges'] > 0 ? Choices::YES : Choices::NO)
                ->setChargesAmt((float) $this->row['Montant charges'])
                ->setDebts($this->findInArray($this->row['Dettes'], self::DEBTS) ?? null)
                ->setIncomeTax($this->findInArray($this->row['AVIS D\'IMPOSITION'], self::YES_NO) ?? null)
                ->setDebtsAmt((float) $this->row['Montant dettes'])
                ->setOverIndebtRecord($this->findInArray($this->row['Plan apurement'], self::OVER_INDEBT_RECORD) ?? null)
                ->setSettlementPlan($this->findInArray($this->row['Plan apurement'], self::SETTLEMENT_PLAN) ?? null)
                ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
                ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
                ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
                ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
                ->setFamilyAllowance(strstr($resourceType, 'PF') ? Choices::YES : 0)
                ->setSalary(strstr($resourceType, 'SALAIRE') ? Choices::YES : 0)
                ->setMaintenance(strstr($resourceType, 'PENSION ALIMENTAIRE') ? Choices::YES : 0)
                ->setRessourceOther($resourceOther ? Choices::YES : 0)
                ->setRessourceOtherPrecision($resourceOther)
                ->setResources($this->findInArray($resourceType, self::RESOURCES) ?? null)
                ->setResourcesAmt((float) $this->row['Montant ressources'])
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

        if ($this->row['Rôle'] == 'CHEF DE FAMILLE') {
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
        } elseif ($this->row['Rôle'] == 'ENFANT') {
            $this->role = RolePerson::ROLE_CHILD;
        } elseif ($this->row['Rôle'] == 'CONJOINT( E )') {
            $this->role = 1;
        }
    }

    protected function getStatus(): int
    {
        if ($this->row['Date diagnostic']) {
            return SupportGroup::STATUS_ENDED;
        }

        if ($this->row['Date sortie']) {
            return SupportGroup::STATUS_ENDED;
        }

        return SupportGroup::STATUS_OTHER;
    }

    protected function getStartDate(): ?\DateTime
    {
        return $this->row['Date diagnostic'] ? new \Datetime($this->row['Date diagnostic']) : null;
    }

    protected function getEndDate(): ?\DateTime
    {
        if ($this->row['Date sortie']) {
            return new \Datetime($this->row['Date sortie']);
        }

        if ($this->row['Date diagnostic']) {
            return new \Datetime($this->row['Date diagnostic']);
        }

        return null;
    }
}
