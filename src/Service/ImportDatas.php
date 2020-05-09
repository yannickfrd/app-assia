<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use App\Entity\User;
use App\Entity\Device;
use App\Entity\Person;
use App\Entity\Service;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\SupportGroup;
use App\Entity\Accommodation;
use App\Entity\InitEvalGroup;
use App\Entity\SupportPerson;
use App\Entity\EvalProfPerson;
use App\Entity\InitEvalPerson;
use App\Entity\EvalBudgetGroup;
use App\Entity\EvaluationGroup;
use App\Entity\EvalBudgetPerson;
use App\Entity\EvalHousingGroup;
use App\Entity\EvalSocialPerson;
use App\Entity\EvaluationPerson;
use App\Entity\AccommodationGroup;
use App\Entity\AccommodationPerson;
use App\Entity\EvalAdmPerson;
use App\Entity\EvalFamilyPerson;
use App\Repository\DeviceRepository;
use App\Repository\PersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ImportDatas
{
    public const FAMILY_TYPOLOGY = [
        'Femme isolée' => 1,
        'Homme isolé' => 2,
        'Couple sans enfant' => 3,
        'Femme avec enfant(s)' => 4,
        'Homme avec enfant(s)' => 5,
        'Couple avec enfant(s)' => 6,
        'Autre' => 9,
    ];

    public const DEVICES = [
        'Insertion Collectif' => 5,
        'Insertion Diffus' => 5,
        'Insertion' => 5,
        'Urgence' => 6,
        'Maison Relais' => 7,
    ];

    public const YES_NO = [
        'Oui' => 1,
        'Non' => 2,
        'En cours' => 3,
        'NR' => 99,
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
        'NR' => 99,
    ];

    public const NATIONALITY = [
        'France' => 1,
        'UE' => 2,
        'Hors-UE' => 3,
        'Apatride' => 4,
        'NR' => 99,
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
        'Autre' => 99,
        'NR' => 99,
    ];

    public const PAPER_TYPE = [
        'Autorisation provisoire de séjour' => 22,
        'Carte de résident (10 ans)' => 20,
        'Carte de séjour temporaire' => 21,
        'Carte d\'identité européenne' => 01,
        'CNI française' => 01,
        'DCEM' => 97, // ??
        'Demandeur d\'asile' => 30, // ??
        'Démarche en cours' => null,
        'OQTF' => null,
        'Récépissé asile' => 30,
        'Récépissé de 1ère demande' => 30,
        'Récépissé renouvellement de titre' => 31,
        'Réfugié' => 20,
        'Sans titre de séjour' => null,
        'Titre de séjour "vie privée et familiale"' => 21,
        'Titre de séjour pour soins' => 21,
        'Titre d\'Identité Républicain (TIR)' => 97, // ??
        'Visa de court séjour' => 97,
        'Visa de long séjour' => 97,
        'Autre' => 97,
        'NR' => 99,
    ];

    public const PROF_STATUS = [
        'Sans emploi' => null,
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
        'NR' => 99,
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
        'NR' => 99,
    ];

    public const RIGHT_SOCIAL_SECURITY = [
        'Sans' => 2,
        'PUMA (ex-CMU)' => 1,
        'CSC (ex-CMU-C)' => 1,
        'AME' => 1,
        'Régime général' => 1,
        'ACS' => 1,
        'En cours' => 3,
        'Autre' => 1,
        'NR' => 99,
    ];

    public const SOCIAL_SECURITY = [
        'Sans' => null, // x
        'PUMA (ex-CMU)' => 3,
        'CSC (ex-CMU-C)' => 4,
        'AME' => 5,
        'Régime général' => 1,
        'ACS' => 6,
        'En cours' => null, // x
        'Autre' => 97,
        'NR' => 99,
    ];

    public const PROTECTIVE_MEASURE = [
        'Non' => 2,
        'MASP' => 1,
        'Sauvegarde de justice' => 1,
        'Curatelle simple' => 1,
        'Curatelle renforcée' => 1,
        'Tutelle' => 1,
        'Autre' => 1,
        'NR' => 99,
    ];

    public const PROTECTIVE_MEASURE_TYPE = [
        'Non' => null,
        'MASP' => 7,
        'Sauvegarde de justice' => 4,
        'Curatelle simple' => 2,
        'Curatelle renforcée' => 3,
        'Tutelle' => 1,
        'Autre' => 97,
        'NR' => 99,
    ];

    public const END_STATUS = [
        'A la rue - abri de fortune' => 001,
        'Accès à la propriété' => 303,
        'CADA' => 400,
        'Colocation' => 304,
        'Décès' => 900,
        'Départ volontaire de la personne' => 700,
        'Détention' => 500,
        'Dispositif hivernal' => 105,
        'Dispositif de soin ou médical (LAM, autre)' => 602,
        'DLSAP' => 502,
        'Exclusion de la structure' => 701,
        'Foyer maternel' => 106,
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
        'Logement adapté - Maison relais' => 203,
        'Logement adapté - Résidence sociale' => 204,
        'Logement adapté - RHVS' => 205,
        'Logement adapté - Solibail/IML' => 206,
        'Logement foyer' => 207,
        'Logement privé' => 300,
        'Logement social' => 301,
        'Maison de retraite' => 305,
        'Placement extérieur' => 501,
        "Retour dans le pays d'origine" => 704,
        'Sous-location' => 302,
        'Squat' => 002,
        'Autre' => 97,
        'NR' => 99,
    ];

    protected $security;
    protected $manager;
    protected $repoDevice;
    protected $repoPerson;

    protected $datas;

    protected $devices = [];
    protected $accommodations = [];
    protected $groups = [];

    protected $gender;
    protected $head;
    protected $role;

    public function __construct(Security $security, EntityManagerInterface $manager, DeviceRepository $repoDevice, PersonRepository	$repoPerson)
    {
        $this->security = $security;
        $this->manager = $manager;
        $this->repoDevice = $repoDevice;
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
                    $date = DateTime::createFromFormat('d/m/Y', $cel, new DateTimeZone(('UTC')));
                    if ($date) {
                        $row[] = $date->format('Y-m-d');
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

    public function importInDatabase(string $fileName, Service $service)
    {
        $this->datas = $this->getDatas($fileName);

        $i = 0;

        foreach ($this->datas as $row) {
            if ($i > 0) {
                $device = $this->getDevice($row);
                $accommodation = $this->getAccommodation($row, $service, $device);

                $typology = $this->findInArray($row[9], self::FAMILY_TYPOLOGY) ?? 9;

                $this->checkGroupExists($row, $typology, $service, $accommodation, $device);

                $groupPeople = $this->groups[$row[0]][0];
                $supportGroup = $this->groups[$row[0]][1];
                $accommodationGroup = $this->groups[$row[0]][2];
                $evaluationGroup = $this->groups[$row[0]][3];

                $this->getRoleAndGender($row, $typology);
                $person = $this->createPerson($row);
                $rolePerson = $this->createRolePerson($groupPeople, $person);
                $supportPerson = $this->createSupportPerson($row, $supportGroup, $person, $rolePerson);
                $this->createAccommodationPerson($person, $accommodationGroup, $supportPerson);
                $evaluationPerson = $this->createEvaluationPerson($row, $evaluationGroup, $supportPerson);
            }
            ++$i;
        }

        // dd($this->accommodations);
        $this->manager->flush();
    }

    protected function getDevice(array $row): Device
    {
        $deviceExists = false;

        foreach ($this->devices as $key => $value) {
            if ($key == $row[2]) {
                $deviceExists = true;
            }
        }

        if (!$deviceExists) {
            $this->devices[$row[2]] = [
                $this->findDevice($row),
            ];
        }

        return  $this->devices[$row[2]][0];
    }

    protected function getAccommodation(array $row, Service $service, Device $device): Accommodation
    {
        $accommodationExists = false;

        foreach ($this->accommodations as $key => $value) {
            if ($key == $row[1]) {
                $accommodationExists = true;
            }
        }

        if (!$accommodationExists) {
            $this->accommodations[$row[1]] = [
                $this->createAccommodation($row, $service, $device),
            ];
        }

        return $this->accommodations[$row[1]][0];
    }

    protected function checkGroupExists(array $row, int $typology, Service $service, Accommodation $accommodation, Device $device)
    {
        $groupExists = false;
        foreach ($this->groups as $key => $value) {
            if ($key == $row[0]) {
                $groupExists = true;
            }
        }

        if (!$groupExists) {
            $groupPeople = $this->createGroupPeople($row, $typology);
            $supportGroup = $this->createSupportGroup($row, $groupPeople, $service, $device);
            $accommodationGroup = $this->createAccommodationGroup($groupPeople, $supportGroup, $accommodation);
            $evaluationGroup = $this->createEvaluationGroup($row, $supportGroup);

            $this->groups[$row[0]] = [
                $groupPeople,
                $supportGroup,
                $accommodationGroup,
                $evaluationGroup,
            ];
        }
    }

    protected function createAccommodation(array $row, Service $service, Device $device): Accommodation
    {
        $accommodation = (new Accommodation())
            ->setName($row[1])
            ->setAddress($row[3])
            ->setPlacesNumber((int) $row[10])
            ->setOpeningDate(new DateTime())
            ->setDevice($device)
            ->setService($service)
            ->setCreatedBy($this->getUser())
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($accommodation);

        return $accommodation;
    }

    protected function createGroupPeople(array $row, int $typology): GroupPeople
    {
        $groupPeople = (new GroupPeople())
                    ->setFamilyTypology($typology)
                    ->setNbPeople((int) $row[10])
                    ->setComment($row[0])
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
                    ->setGroupPeople($groupPeople)
                    ->setService($service)
                    ->setDevice($device)
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedBy($this->getUser());

        $this->manager->persist($supportGroup);

        return $supportGroup;
    }

    protected function createAccommodationGroup(GroupPeople $groupPeople, SupportGroup $supportGroup, Accommodation $accommodation): AccommodationGroup
    {
        $accommodationGroup = (new AccommodationGroup())
            ->setStartDate($supportGroup->getStartDate() ? $supportGroup->getStartDate() : null)
            ->setEndDate($supportGroup->getEndDate() ? $supportGroup->getEndDate() : null)
            ->setEndReason($supportGroup->getEndDate() ? 1 : null)
            ->setGroupPeople($groupPeople)
            ->setSupportGroup($supportGroup)
            ->setAccommodation($accommodation)
            ->setCreatedBy($this->getUser())
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($accommodationGroup);

        return $accommodationGroup;
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

        $this->createEvalBudgetGroup($row, $evaluationGroup);
        $this->createEvalHousingGroup($row, $evaluationGroup);

        return $evaluationGroup;
    }

    protected function createInitEvalGroup(array $row, SupportGroup $supportGroup): InitEvalGroup
    {
        $initEvalGroup = (new InitEvalGroup())
            ->setSupportGroup($supportGroup)
            ->setHousingStatus($this->findInArray($row[22], self::HOUSING_STATUS) ?? null)
            ->setSiaoRequest($this->findInArray($row[65], self::YES_NO) ?? null)
            ->setSocialHousingRequest($this->findInArray($row[47], self::YES_NO) ?? null)
            ->setResourcesGroupAmt((float) $row[39]);

        $this->manager->persist($initEvalGroup);

        return $initEvalGroup;
    }

    protected function createEvalBudgetGroup(array $row, EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setEvaluationGroup($evaluationGroup)
            ->setResourcesGroupAmt((float) $row[58])
            ->setBudgetBalanceAmt((float) $row[58]);

        $this->manager->persist($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(array $row, EvaluationGroup $evaluationGroup): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
            ->setEvaluationGroup($evaluationGroup)
            ->setHousingStatus($this->findInArray($row[22], self::HOUSING_STATUS) ?? null)
            ->setSiaoRequest($this->findInArray($row[65], self::YES_NO) ?? null)
            ->setSocialHousingRequest($this->findInArray($row[68], self::YES_NO) ?? null);

        $this->manager->persist($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function createPerson(array $row): Person
    {
        $person = (new Person())
                    ->setLastname($row[5])
                    ->setFirstname($row[6])
                    ->setBirthdate(new Datetime($row[7]))
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

    protected function createAccommodationPerson(Person $person, AccommodationGroup $accommodationGroup, SupportPerson $supportPerson): AccommodationPerson
    {
        $accommodationPerson = (new AccommodationPerson())
            ->setStartDate($supportPerson->getStartDate() ? $supportPerson->getStartDate() : null)
            ->setEndDate($supportPerson->getEndDate() ? $supportPerson->getEndDate() : null)
            ->setEndReason($supportPerson->getEndDate() ? 1 : null)
            ->setAccommodationGroup($accommodationGroup)
            ->setPerson($person)
            ->setCreatedBy($this->getUser())
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($accommodationPerson);

        return $accommodationPerson;
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
        $this->createEvalProfPerson($row, $evaluationPerson);
        $this->createEvalBudgetPerson($row, $evaluationPerson);

        return $evaluationPerson;
    }

    protected function createInitEvalPerson(array $row, SupportPerson $supportPerson): InitEvalPerson
    {
        $initEvalPerson = (new InitEvalPerson())
            ->setSupportPerson($supportPerson)
            ->setPaperType($this->findInArray($row[30], self::PAPER_TYPE) ?? null)
            ->setRightSocialSecurity($this->findInArray($row[41], self::RIGHT_SOCIAL_SECURITY) ?? null)
            ->setSocialSecurity($this->findInArray($row[41], self::SOCIAL_SECURITY) ?? null)
            ->setFamilyBreakdown($this->findInArray($row[26], self::YES_NO) ?? null)
            ->setFriendshipBreakdown($this->findInArray($row[26], self::YES_NO) ?? null)
            ->setProfStatus($this->findInArray($row[40], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($row[40], self::CONTRACT_TYPE) ?? null)
            ->setResources($row[32] == 'Oui' ? true : null)
            ->setResourcesAmt((float) $row[38])
            ->setUnemplBenefit($row[34] == 'Oui' ? true : null)
            ->setMinimumIncome($row[35] == 'Oui' ? true : null)
            ->setFamilyAllowance($row[36] == 'Oui' ? true : null)
            ->setSalary($row[33] == 'Oui' ? true : null)
            ->setRessourceOther($row[37] ? true : null)
            ->setRessourceOtherPrecision($row[37]);

        $this->manager->persist($initEvalPerson);

        return $initEvalPerson;
    }

    protected function createEvalSocialPerson(array $row, EvaluationPerson $evaluationPerson): EvalSocialPerson
    {
        $evalSocialPerson = (new EvalSocialPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setRightSocialSecurity($this->findInArray($row[41], self::RIGHT_SOCIAL_SECURITY) ?? null)
            ->setSocialSecurity($this->findInArray($row[41], self::SOCIAL_SECURITY) ?? null)
            ->setFamilyBreakdown($this->findInArray($row[65], self::YES_NO) ?? null)
            ->setFriendshipBreakdown($this->findInArray($row[65], self::YES_NO) ?? null)
            ->setChildWelfareBackground($this->findInArray($row[25], self::YES_NO) ?? null);

        $this->manager->persist($evalSocialPerson);

        return $evalSocialPerson;
    }

    protected function createEvalFamilyPerson(array $row, EvaluationPerson $evaluationPerson): EvalFamilyPerson
    {
        $evalFamilyPerson = (new EvalFamilyPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setProtectiveMeasure($this->findInArray($row[61], self::PROTECTIVE_MEASURE) ?? null)
            ->setProtectiveMeasureType($this->findInArray($row[61], self::PROTECTIVE_MEASURE_TYPE) ?? null);

        $this->manager->persist($evalFamilyPerson);

        return $evalFamilyPerson;
    }

    protected function createEvalAdmPerson(array $row, EvaluationPerson $evaluationPerson): EvalAdmPerson
    {
        $evalAdmPerson = (new EvalAdmPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setNationality($this->findInArray($row[28], self::NATIONALITY) ?? null)
            ->setCountry($row[29])
            ->setPaper($this->findInArray($row[50], self::PAPER) ?? null)
            ->setPaperType($this->findInArray($row[50], self::PAPER_TYPE) ?? null)
            ->setAsylumBackground($this->findInArray($row[31], self::YES_NO) ?? null)
            ->setCommentEvalAdmPerson($row[50]);

        $this->manager->persist($evalAdmPerson);

        return $evalAdmPerson;
    }

    protected function createEvalProfPerson(array $row, EvaluationPerson $evaluationPerson): EvalProfPerson
    {
        $evalProfPerson = (new EvalProfPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setProfStatus($this->findInArray($row[40], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($row[40], self::CONTRACT_TYPE) ?? null);

        $this->manager->persist($evalProfPerson);

        return $evalProfPerson;
    }

    protected function createEvalBudgetPerson(array $row, EvaluationPerson $evaluationPerson): EvalBudgetPerson
    {
        $evalBudgetPerson = (new EvalBudgetPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setResources($row[51] == 'Oui' ? true : null)
            ->setResourcesAmt((float) $row[57])
            ->setUnemplBenefit($row[53] == 'Oui' ? true : null)
            ->setMinimumIncome($row[54] == 'Oui' ? true : null)
            ->setFamilyAllowance($row[55] == 'Oui' ? true : null)
            ->setSalary($row[52] == 'Oui' ? true : null)
            ->setRessourceOther($row[55] ? true : null)
            ->setRessourceOtherPrecision($row[56]);

        $this->manager->persist($evalBudgetPerson);

        return $evalBudgetPerson;
    }

    protected function findDevice(array $row): ?Device
    {
        foreach (self::DEVICES as $key => $value) {
            if ($key == $row[2]) {
                return $this->repoDevice->find($value);
            }
        }

        dd('Dispositif inconnu !');
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

    protected function getRoleAndGender(array $row, int $typology)
    {
        $this->gender = 99;
        $this->head = false;
        $this->role = 7;

        if ($row[11] == ' DP') {
            $this->head = true;
            if (in_array($typology, [1, 4])) {
                $this->gender = 1;
            }
            if (in_array($typology, [2, 5])) {
                $this->gender = 2;
            }
            if (in_array($typology, [1, 2])) {
                $this->role = 5;
            } elseif (in_array($typology, [4, 6])) {
                $this->role = 4;
            } elseif (in_array($typology, [3, 6, 7, 8])) {
                $this->role = 1;
            }
        } elseif ($row[11] == 'Enfant') {
            $this->role = 3;
        } elseif ($row[11] == 'Conjoint·e') {
            $this->role = 1;
        }
    }

    protected function getStatus(array $row): int
    {
        return $row[74] ? 4 : ($row[21] ? 2 : 5);
    }

    protected function getStartDate(array $row): ?DateTime
    {
        return $row[21] ? new Datetime($row[21]) : null;
    }

    protected function getEndDate(array $row): ?DateTime
    {
        return $row[74] ? new Datetime($row[74]) : null;
    }

    protected function getUser(): User
    {
        return $this->security->getUser();
    }
}
