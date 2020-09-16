<?php

namespace App\Service\Import;

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
use App\Entity\User;
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

    public const SIAO_DEPT = [
        'SIAO 75' => 75,
        'SIAO 77' => 77,
        'SIAO 78' => 78,
        'SIAO 92' => 92,
        'SIAO 93' => 93,
        'SIAO 94' => 94,
        'SIAO 95' => 95,
    ];
    public const SIAO_RECOMMENDATION = [
    'Hébergement' => 1,
    'Sorti d\'hôtel' => 99,
    'Logement Intermédiaire' => 2,
    'Logement ' => 3,
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

    protected $security;
    protected $manager;
    protected $repoPerson;

    protected $datas;

    protected $device;
    protected $groups = [];

    protected $gender;
    protected $head;
    protected $role;

    public function __construct(Security $security, EntityManagerInterface $manager, DeviceRepository $repoDevice, PersonRepository $repoPerson)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->device = $repoDevice->find(15); // Numéro à confirmer
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
            if ($i > 0) {
                $typology = $this->findInArray($row['Compo'], self::FAMILY_TYPOLOGY) ?? 9;

                $this->checkGroupExists($row, $typology, $service, $this->device);

                $groupPeople = $this->groups[$row['ID_GIP']][0];
                $supportGroup = $this->groups[$row['ID_GIP']][1];
                $evaluationGroup = $this->groups[$row['ID_GIP']][2];

                $this->getRole($row, $typology);
                $person = $this->createPerson($row);
                $rolePerson = $this->createRolePerson($groupPeople, $person);
                $supportPerson = $this->createSupportPerson($row, $supportGroup, $person, $rolePerson);
                $evaluationPerson = $this->createEvaluationPerson($row, $evaluationGroup, $supportPerson);
            }
            ++$i;
        }

        // dd($this->groups);
        $this->manager->flush();
    }

    protected function checkGroupExists(array $row, int $typology, Service $service, Device $device)
    {
        $groupExists = false;
        foreach ($this->groups as $key => $value) {
            if ($key == $row['ID_GIP']) {
                $groupExists = true;
            }
        }

        if (!$groupExists) {
            $groupPeople = $this->createGroupPeople($row, $typology);
            $supportGroup = $this->createSupportGroup($row, $groupPeople, $service, $device);
            $evaluationGroup = $this->createEvaluationGroup($row, $supportGroup);

            $this->groups[$row['ID_GIP']] = [
                $groupPeople,
                $supportGroup,
                $evaluationGroup,
            ];
        }
    }

    protected function createGroupPeople(array $row, int $typology): GroupPeople
    {
        $groupPeople = (new GroupPeople())
                    ->setFamilyTypology($typology)
                    ->setNbPeople($this->findInArray($row['Compo'], self::NB_PEOPLE) ?? null)
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedBy($this->getUser());

        $this->manager->persist($groupPeople);

        return $groupPeople;
    }

    protected function createSupportGroup(array $row, GroupPeople $groupPeople, Service $service, Device $device): SupportGroup
    {
        $supportGroup = (new SupportGroup())
                    ->setStatus($this->getStatus($row))
                    ->setStartDate($this->getStartDate($row))
                    ->setEndDate($this->getEndDate($row))
                    ->setEndStatus(null)
                    ->setEndStatusComment($row['Motif sortie'])
                    ->setNbPeople($this->findInArray($row['Compo'], self::NB_PEOPLE) ?? null)
                    ->setGroupPeople($groupPeople)
                    ->setService($service)
                    ->setDevice($device)
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedBy($this->getUser());

        $this->manager->persist($supportGroup);

        if ($row['Date diagnostic']) {
            $this->createHotelSupport($row, $supportGroup);
        }

        return $supportGroup;
    }

    protected function createEvaluationGroup(array $row, $supportGroup): EvaluationGroup
    {
        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setInitEvalGroup($this->createInitEvalGroup($row, $supportGroup))
            ->setDate($supportGroup->getCreatedAt())
            ->setCreatedAt($supportGroup->getCreatedAt())
            ->setUpdatedAt($supportGroup->getUpdatedAt())
            ->setCreatedBy($this->getUser())
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($evaluationGroup);

        $this->createEvalSocialGroup($row, $evaluationGroup);
        $this->createEvalFamilyGroup($row, $evaluationGroup);
        $this->createEvalBudgetGroup($row, $evaluationGroup);
        $this->createEvalHousingGroup($row, $evaluationGroup);

        return $evaluationGroup;
    }

    protected function createInitEvalGroup(array $row, SupportGroup $supportGroup): InitEvalGroup
    {
        $initEvalGroup = (new InitEvalGroup())
        ->setSiaoRequest($this->findInArray($row['Demande SIAO préalable au diag'], self::YES_NO) ?? null)
        ->setSocialHousingRequest($this->findInArray($row['Demande de logement social active'], self::DLS) ?? null)
        ->setResourcesGroupAmt((float) $row['Montant ressources'])
        ->setDebtsGroupAmt((float) $row['Montant dettes'])
        ->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        return $initEvalGroup;
    }

    protected function createEvalSocialGroup(array $row, EvaluationGroup $evaluationGroup): EvalSocialGroup
    {
        $evalSocialGroup = (new EvalSocialGroup())
            ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalSocialGroup);

        return $evalSocialGroup;
    }

    protected function createEvalFamilyGroup(array $row, EvaluationGroup $evaluationGroup): EvalFamilyGroup
    {
        $evalFamilyGroup = (new EvalFamilyGroup())
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalFamilyGroup);

        return $evalFamilyGroup;
    }

    protected function createEvalBudgetGroup(array $row, EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setEvaluationGroup($evaluationGroup)
            ->setResourcesGroupAmt((float) $row['Montant ressources'])
            ->setChargesGroupAmt((float) $row['Montant charges'])
            ->setDebtsGroupAmt((float) $row['Montant dettes']);
        // ->setBudgetBalanceAmt((float) ($row['Montant ressources'] - $row['Montant charges']));

        $this->manager->persist($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(array $row, EvaluationGroup $evaluationGroup): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
        ->setHousingStatus(null)
        ->setSiaoRequest($row['Date demande initiale SIAO'] ? Choices::YES : Choices::NO)
        ->setSiaoRequestDate($row['Date demande initiale SIAO'] ? new \Datetime($row['Date demande initiale SIAO']) : null)
        ->setSiaoUpdatedRequestDate($row['Date dernière actualisation SIAO'] ? new \Datetime($row['Date dernière actualisation SIAO']) : null)
        ->setSiaoRequestDept($this->findInArray($row['SIAO prescripteur'], self::SIAO_DEPT) ?? null)
        // ->setSiaoRecommendation($this->findInArray($row['Préconisation'], self::SIAO_RECOMMENDATION) ?? null)
        ->setSocialHousingRequest($this->findInArray($row['Demande de logement social active'], self::DLS) ?? null)
        ->setSocialHousingRequestId($row['NUR'])
        ->setDaloCommission($this->findInArray($row['DALO / DAHO'], self::DALO_COMMISSION) ?? null)
        ->setDaloRequalifiedDaho($this->findInArray($row['DALO / DAHO'], self::DALO_REQUALIFIED_DAHO) ?? null)
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function createPerson(array $row): Person
    {
        $person = (new Person())
                    ->setLastname($row['Nom'])
                    ->setFirstname($row['Prénom'])
                    ->setBirthdate($row['Date naissance'] ? new \Datetime($row['Date naissance']) : null)
                    ->setGender($this->gender)
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedBy($this->getUser());

        $personExists = $this->personExists($person);

        if ($personExists) {
            $person = $personExists;
        } else {
            $this->manager->persist($person);
        }

        return $person;
    }

    protected function personExists($person)
    {
        return $this->repoPerson->findOneBy([
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'birthdate' => $person->getBirthdate(),
        ]);
    }

    protected function createRolePerson(GroupPeople $groupPeople, Person $person): RolePerson
    {
        $this->rolePerson = (new RolePerson())
                 ->setHead($this->head)
                 ->setRole($this->role)
                 ->setPerson($person)
                 ->setGroupPeople($groupPeople);

        $this->manager->persist($this->rolePerson);

        return $this->rolePerson;
    }

    protected function createSupportPerson(array $row, SupportGroup $supportGroup, Person $person, RolePerson $rolePerson): SupportPerson
    {
        $supportPerson = (new SupportPerson())
                    ->setStatus($this->getStatus($row))
                    ->setStartDate($this->getStartDate($row))
                    ->setEndDate($this->getEndDate($row))
                    ->setSupportGroup($supportGroup)
                    ->setPerson($person)
                    ->setHead($rolePerson->getHead())
                    ->setRole($rolePerson->getRole())
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedBy($this->getUser());

        $this->manager->persist($supportPerson);

        return $supportPerson;
    }

    protected function createHotelSupport(array $row, SupportGroup $supportGroup): HotelSupport
    {
        $hotelSupport = (new HotelSupport())
            ->setGipId($row['ID_GIP'])
            ->setDiagStartDate($row['Date diagnostic'] ? new \Datetime($row['Date diagnostic']) : null)
            ->setDiagComment($row['TS diagnostic'] ? 'TS : '.$row['TS diagnostic'] : null)
            ->setSupportStartDate($this->getStartDate($row))
            ->setSupportEndDate($this->getEndDate($row))
            ->setSupportComment($row['TS accompagnement'] ? 'TS : '.$row['TS accompagnement'] : null)
            ->setSupportGroup($supportGroup);

        $this->manager->persist($hotelSupport);

        return $hotelSupport;
    }

    protected function createEvaluationPerson(array $row, EvaluationGroup $evaluationGroup, SupportPerson $supportPerson): EvaluationPerson
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson)
            ->setInitEvalPerson($this->createInitEvalPerson($row, $supportPerson))
            ->setCreatedBy($this->getUser())
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($evaluationPerson);

        $this->createEvalSocialPerson($row, $evaluationPerson);
        $this->createEvalAdmPerson($row, $evaluationPerson);
        $this->createEvalBudgetPerson($row, $evaluationPerson);
        $this->createEvalFamilyPerson($row, $evaluationPerson);
        $this->createEvalProfPerson($row, $evaluationPerson);

        return $evaluationPerson;
    }

    protected function createInitEvalPerson(array $row, SupportPerson $supportPerson): InitEvalPerson
    {
        $resourceType = $row['Type ressources'];
        $resourceOther = null;

        if ($resourceType == 'AIDE EXTERIEURE') {
            $resourceOther = 'Aide extérieure';
        }
        if ($resourceType == 'RESSOURCES NON DECLAREES') {
            $resourceOther = 'Ressources non déclarées';
        }

        $initEvalPerson = (new InitEvalPerson())
            ->setPaperType($this->findInArray($row['Situation administrative'], self::PAPER_TYPE) ?? null)
            ->setRightSocialSecurity($this->findInArray($row['Couverture maladie'], self::RIGHT_SOCIAL_SECURITY) ?? null)
            ->setSocialSecurity($this->findInArray($row['Couverture maladie'], self::SOCIAL_SECURITY) ?? null)
            ->setProfStatus($this->findInArray($row['Emploi'], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($row['Emploi'], self::CONTRACT_TYPE) ?? null)
            ->setResources($this->findInArray($resourceType, self::RESOURCES) ?? null)
            ->setResourcesAmt((float) $row['Montant ressources'])
            ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
            ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
            ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
            ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
            ->setFamilyAllowance(strstr($resourceType, 'PF') ? Choices::YES : 0)
            ->setSalary(strstr($resourceType, 'SALAIRE') ? Choices::YES : 0)
            ->setMaintenance(strstr($resourceType, 'PENSION ALIMENTAIRE') ? Choices::YES : 0)
            ->setRessourceOther($resourceOther ? Choices::YES : 0)
            ->setRessourceOtherPrecision($resourceOther)
            ->setDebts($this->findInArray($row['Dettes'], self::DEBTS) ?? null)
            ->setDebtsAmt((float) $row['Montant dettes'])
            ->setSupportPerson($supportPerson);

        $this->manager->persist($initEvalPerson);

        return $initEvalPerson;
    }

    protected function createEvalSocialPerson(array $row, EvaluationPerson $evaluationPerson): EvalSocialPerson
    {
        $evalSocialPerson = (new EvalSocialPerson())
        ->setRightSocialSecurity($this->findInArray($row['Couverture maladie'], self::RIGHT_SOCIAL_SECURITY) ?? null)
        ->setSocialSecurity($this->findInArray($row['Couverture maladie'], self::SOCIAL_SECURITY) ?? null)
        ->setEndRightsSocialSecurityDate($row['Date fin validité Sécurité sociale'] ? new \Datetime($row['Date fin validité Sécurité sociale']) : null)
        ->setCommentEvalSocialPerson($row['Suivi social'] ? 'Suivi social : Oui' : null)
        ->setEvaluationPerson($evaluationPerson);

        $this->manager->persist($evalSocialPerson);

        return $evalSocialPerson;
    }

    protected function createEvalFamilyPerson(array $row, EvaluationPerson $evaluationPerson): EvalFamilyPerson
    {
        $evalFamilyPerson = (new EvalFamilyPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setMaritalStatus($this->findInArray($row['Situation matrimoniale'], self::MARITAL_STATUS) ?? null)
            ->setUnbornChild($this->findInArray($row['Grossesse'], self::YES_NO) ?? null);

        $this->manager->persist($evalFamilyPerson);

        return $evalFamilyPerson;
    }

    protected function createEvalAdmPerson(array $row, EvaluationPerson $evaluationPerson): EvalAdmPerson
    {
        $evalAdmPerson = (new EvalAdmPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setNationality($this->findInArray($row['Nationalité'], self::NATIONALITY) ?? null)
            ->setArrivalDate($row['Date arrivée France'] ? new \Datetime($row['Date arrivée France']) : null)
            ->setPaper($this->findInArray($row['Situation administrative'], self::PAPER) ?? null)
            ->setPaperType($this->findInArray($row['Situation administrative'], self::PAPER_TYPE) ?? null)
            ->setEndValidPermitDate($row['Date fin validité titre'] ? new \Datetime($row['Date fin validité titre']) : null)
            ->setAsylumBackground($this->findInArray($row['Situation administrative'], self::ASYLUM_BACKGROUND) ?? null)
            ->setCommentEvalAdmPerson(null);

        $this->manager->persist($evalAdmPerson);

        return $evalAdmPerson;
    }

    protected function createEvalProfPerson(array $row, EvaluationPerson $evaluationPerson): EvalProfPerson
    {
        $evalProfPerson = (new EvalProfPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setProfStatus($this->findInArray($row['Emploi'], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($row['Emploi'], self::CONTRACT_TYPE) ?? null);

        $this->manager->persist($evalProfPerson);

        return $evalProfPerson;
    }

    protected function createEvalBudgetPerson(array $row, EvaluationPerson $evaluationPerson): EvalBudgetPerson
    {
        $resourceType = $row['Type ressources'];
        $resourceOther = null;

        if ($resourceType == 'AIDE EXTERIEURE') {
            $resourceOther = 'Aide extérieure';
        }
        if ($resourceType == 'RESSOURCES NON DECLAREES') {
            $resourceOther = 'Ressources non déclarées';
        }

        $evalBudgetPerson = (new EvalBudgetPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setChargesAmt((float) $row['Montant charges'])
            ->setDebts($this->findInArray($row['Dettes'], self::DEBTS) ?? null)
            ->setDebtsAmt((float) $row['Montant dettes'])
            ->setSettlementPlan($this->findInArray($row['Plan apurement'], self::YES_NO) ?? null)
            ->setDisAdultAllowance(strstr($resourceType, 'AAH') ? Choices::YES : 0)
            ->setAsylumAllowance(strstr($resourceType, 'ADA') ? Choices::YES : 0)
            ->setUnemplBenefit(strstr($resourceType, 'ARE') ? Choices::YES : 0)
            ->setMinimumIncome(strstr($resourceType, 'RSA') ? Choices::YES : 0)
            ->setFamilyAllowance(strstr($resourceType, 'PF') ? Choices::YES : 0)
            ->setSalary(strstr($resourceType, 'SALAIRE') ? Choices::YES : 0)
            ->setMaintenance(strstr($resourceType, 'PENSION ALIMENTAIRE') ? Choices::YES : 0)
            ->setRessourceOther($resourceOther ? Choices::YES : 0)
            ->setRessourceOtherPrecision($resourceOther)
            ->setResources($this->findInArray($row['Type ressources'], self::RESOURCES) ?? null)
            ->setResourcesAmt((float) $row['Montant ressources']);

        $this->manager->persist($evalBudgetPerson);

        return $evalBudgetPerson;
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

    protected function getRole(array $row, int $typology)
    {
        $this->gender = 99;
        $this->head = false;
        $this->role = 97;

        if ($row['Rôle'] == 'CHEF DE FAMILLE') {
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
        } elseif ($row['Rôle'] == 'ENFANT') {
            $this->role = RolePerson::ROLE_CHILD;
        } elseif ($row['Rôle'] == 'CONJOINT( E )') {
            $this->role = 1;
        }
    }

    protected function getStatus(array $row): int
    {
        if ($row['Date entrée']) {
            return SupportGroup::STATUS_IN_PROGRESS;
        }

        if ($row['Date sortie'] || $row['Date diagnostic']) {
            return SupportGroup::STATUS_ENDED;
        }

        return SupportGroup::STATUS_OTHER;
    }

    protected function getStartDate(array $row): ?\DateTime
    {
        return $row['Date entrée'] ? new \Datetime($row['Date entrée']) : null;
    }

    protected function getEndDate(array $row): ?\DateTime
    {
        return $row['Date sortie'] ? new \Datetime($row['Date sortie']) : null;
    }

    protected function getUser(): User
    {
        return $this->security->getUser();
    }

    protected function createNote(SupportGroup $supportGroup, string $title, string $content): Note
    {
        $note = (new Note())
        ->setTitle($title)
        ->setContent($content)
        ->setSupportGroup($supportGroup)
        ->setCreatedBy($this->getUser())
        ->setUpdatedBy($this->getUser());

        $this->manager->persist($note);

        return $note;
    }
}
