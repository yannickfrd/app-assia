<?php

namespace App\Service;

use DateTime;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\Device;
use App\Entity\Person;
use App\Entity\Service;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\SupportGroup;
use App\Entity\Accommodation;
use App\Entity\EvalAdmPerson;
use App\Entity\InitEvalGroup;
use App\Entity\OriginRequest;
use App\Entity\SupportPerson;
use App\Entity\EvalProfPerson;
use App\Entity\InitEvalPerson;
use App\Entity\EvalBudgetGroup;
use App\Entity\EvalFamilyGroup;
use App\Entity\EvalSocialGroup;
use App\Entity\EvaluationGroup;
use App\Entity\EvalBudgetPerson;
use App\Entity\EvalFamilyPerson;
use App\Entity\EvalHousingGroup;
use App\Entity\EvalSocialPerson;
use App\Entity\EvaluationPerson;
use App\Entity\AccommodationGroup;
use App\Entity\AccommodationPerson;
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
        'NR' => 99,
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
        'NR' => 99,
    ];

    public const CARE_SUPPORT = [
        'Non' => 2,
        'Infirmier à domicile' => 1,
        'PCH' => 1,
        'SAMSAH' => 1,
        'SAVS' => 1,
        'Autre' => 1,
        'Non renseignée' => 99,
    ];
    public const CARE_SUPPORT_TYPE = [
        'Infirmier à domicile' => 1,
        'PCH' => 2,
        'SAMSAH' => 3,
        'SAVS' => 4,
        'Autre' => 97,
        'Non renseignée' => 99,
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

    public const CHILDCARE_SCHOOL = [
        'Assistante maternelle' => 4,
        'Crèche' => 1,
        'Ecole' => 2,
        'Parent' => 3,
        'Nourrice' => 5,
        'Autre' => 97,
        'NR' => 99,
    ];

    public const PROF_STATUS = [
        'Sans emploi' => 97,
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
        'CMU' => 1,
        'CMU-C' => 1,
        'AME' => 1,
        'Régime Général' => 1,
        'ACS' => 1,
        'En cours' => 3,
        'Autre' => 1,
        'NR' => 99,
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
                    $date = DateTime::createFromFormat('d/m/Y', $cel, new \DateTimeZone(('UTC')));
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
                $device = $this->getDevice($row);
                $accommodation = $this->getAccommodation($row, $service, $device);

                $typology = $this->findInArray($row['Typologie familiale'], self::FAMILY_TYPOLOGY) ?? 9;

                $this->checkGroupExists($row, $typology, $service, $accommodation, $device);

                $groupPeople = $this->groups[$row['N° ménage']][0];
                $supportGroup = $this->groups[$row['N° ménage']][1];
                $accommodationGroup = $this->groups[$row['N° ménage']][2];
                $evaluationGroup = $this->groups[$row['N° ménage']][3];

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
            if ($key == $row['Dispositif']) {
                $deviceExists = true;
            }
        }

        if (!$deviceExists) {
            $this->devices[$row['Dispositif']] = [
                $this->findDevice($row),
            ];
        }

        return  $this->devices[$row['Dispositif']][0];
    }

    protected function getAccommodation(array $row, Service $service, Device $device): Accommodation
    {
        $accommodationExists = false;

        foreach ($this->accommodations as $key => $value) {
            if ($key == $row['Nom place']) {
                $accommodationExists = true;
            }
        }

        if (!$accommodationExists) {
            $this->accommodations[$row['Nom place']] = [
                $this->createAccommodation($row, $service, $device),
            ];
        }

        return $this->accommodations[$row['Nom place']][0];
    }

    protected function checkGroupExists(array $row, int $typology, Service $service, Accommodation $accommodation, Device $device)
    {
        $groupExists = false;
        foreach ($this->groups as $key => $value) {
            if ($key == $row['N° ménage']) {
                $groupExists = true;
            }
        }

        if (!$groupExists) {
            $groupPeople = $this->createGroupPeople($row, $typology);
            $supportGroup = $this->createSupportGroup($row, $groupPeople, $service, $device);
            $accommodationGroup = $this->createAccommodationGroup($groupPeople, $supportGroup, $accommodation);
            $evaluationGroup = $this->createEvaluationGroup($row, $supportGroup);

            $this->groups[$row['N° ménage']] = [
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
            ->setName($row['Nom place'])
            ->setAddress($row['Adresse logement'])
            ->setNbPlaces((int) $row['Nb pers'])
            ->setStartDate(new DateTime('2019-01-01'))
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
                    ->setNbPeople((int) $row['Nb pers'])
                    ->setComment($row['N° ménage'])
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
                    ->setEndStatus($this->findInArray($row['Type sortie'], self::END_STATUS) ?? null)
                    ->setEndStatusComment($row['Commentaire sur la sortie'])
                    ->setGroupPeople($groupPeople)
                    ->setService($service)
                    ->setDevice($device)
                    ->setCreatedBy($this->getUser())
                    ->setUpdatedBy($this->getUser())
                    ->setComment($row['Référent social'] ? 'Référent social : '.$row['Référent social'] : null);

        $this->manager->persist($supportGroup);

        if ($row['Service prescripteur'] || $row['Date entretien pré-admission']) {
            $this->createOriginRequest($row, $supportGroup);
        }

        if ($row['Accompagnement social mis en place']) {
            $this->createNote($supportGroup, 'Accompagnement social mis en place', $row['Accompagnement social mis en place']);
        }

        return $supportGroup;
    }

    protected function createOriginRequest(array $row, SupportGroup $supportGroup): OriginRequest
    {
        $originRequest = (new OriginRequest())
        ->setOrganizationComment($row['Service prescripteur'])
        ->setPreAdmissionDate($row['Date entretien pré-admission'] ? new \Datetime($row['Date entretien pré-admission']) : null)
        ->setResulPreAdmission($this->findInArray($row['Résultat entretien pré-admission'], self::RESULT_PRE_ADMISSION) ?? null)
        ->setComment($row['Commentaire pré-admission'])
        ->setSupportGroup($supportGroup);

        $this->manager->persist($originRequest);

        return $originRequest;
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

        $this->createEvalSocialGroup($row, $evaluationGroup);
        $this->createEvalFamilyGroup($row, $evaluationGroup);
        $this->createEvalBudgetGroup($row, $evaluationGroup);
        $this->createEvalHousingGroup($row, $evaluationGroup);

        return $evaluationGroup;
    }

    protected function createInitEvalGroup(array $row, SupportGroup $supportGroup): InitEvalGroup
    {
        $initEvalGroup = (new InitEvalGroup())
        ->setHousingStatus($this->findInArray($row['Situation résidentielle (avant entrée)'], self::HOUSING_STATUS) ?? null)
        ->setSiaoRequest($this->findInArray($row['Demande SIAO active'], self::YES_NO) ?? null)
        ->setSocialHousingRequest($this->findInArray($row['Demande logement social (entrée)'], self::YES_NO) ?? null)
        ->setResourcesGroupAmt((float) $row['Total ressources ménage (entrée)'])
        ->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        return $initEvalGroup;
    }

    protected function createEvalSocialGroup(array $row, EvaluationGroup $evaluationGroup): EvalSocialGroup
    {
        $evalSocialGroup = (new EvalSocialGroup())
            ->setReasonRequest($this->findInArray($row['Raison principale de la demande'], self::REASON_REQUEST) ?? null)
            ->setWanderingTime($this->getWanderingTime((float) $row['Durée d\'errance']))
            ->setCommentEvalSocialGroup($row['Commentaire situation résidentielle'])
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

        return 99;
    }

    protected function createEvalFamilyGroup(array $row, EvaluationGroup $evaluationGroup): EvalFamilyGroup
    {
        $evalFamilyGroup = (new EvalFamilyGroup())
        ->setChildrenBehind((int) $row['Enfants au pays'])
        // ->setCommentEvalFamilyGroup($row['Commentairesituation familiale'])
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalFamilyGroup);

        return $evalFamilyGroup;
    }

    protected function createEvalBudgetGroup(array $row, EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setEvaluationGroup($evaluationGroup)
            ->setResourcesGroupAmt((float) $row['Total ressources ménage'])
            ->setBudgetBalanceAmt((float) $row['Total ressources ménage']);

        $this->manager->persist($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(array $row, EvaluationGroup $evaluationGroup): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
        ->setHousingStatus($this->findInArray($row['Situation résidentielle (avant entrée)'], self::HOUSING_STATUS) ?? null)
        ->setSiaoRequest($this->findInArray($row['Demande SIAO active'], self::YES_NO) ?? null)
        ->setSiaoRequestDate($row['Date demande initiale SIAO'] ? new \Datetime($row['Date demande initiale SIAO']) : null)
        ->setSiaoUpdatedRequestDate($row['Date dernière actualisation SIAO'] ? new \Datetime($row['Date dernière actualisation SIAO']) : null)
        ->setSocialHousingRequest($this->findInArray($row['Demande de logement social active'], self::YES_NO) ?? null)
        ->setSocialHousingRequestDate($row['Date demande de logement social'] ? new \Datetime($row['Date demande de logement social']) : null)
        ->setCommentEvalHousing($row['Modalité de sortie vers le logement'] ? 'Modalité de sortie vers le logement : '.$row['Modalité de sortie vers le logement'] : null)
        ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function createPerson(array $row): Person
    {
        $person = (new Person())
                    ->setLastname($row['Nom ménage'])
                    ->setFirstname($row['Prénom'])
                    ->setBirthdate(new \Datetime($row['Date naissance']))
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
        $this->createEvalBudgetPerson($row, $evaluationPerson);
        $this->createEvalFamilyPerson($row, $evaluationPerson);
        $this->createEvalProfPerson($row, $evaluationPerson);

        return $evaluationPerson;
    }

    protected function createInitEvalPerson(array $row, SupportPerson $supportPerson): InitEvalPerson
    {
        $initEvalPerson = (new InitEvalPerson())
            ->setSupportPerson($supportPerson)
            ->setPaperType($this->findInArray($row['Situation administrative (entrée)'], self::PAPER_TYPE) ?? null)
            ->setRightSocialSecurity($this->findInArray($row['Couverture maladie (entrée)'], self::RIGHT_SOCIAL_SECURITY) ?? null)
            ->setSocialSecurity($this->findInArray($row['Couverture maladie (entrée)'], self::SOCIAL_SECURITY) ?? null)
            ->setFamilyBreakdown($this->findInArray($row['Rupture liens familiaux et amicaux'], self::YES_NO) ?? null)
            ->setFriendshipBreakdown($this->findInArray($row['Rupture liens familiaux et amicaux'], self::YES_NO) ?? null)
            ->setProfStatus($this->findInArray($row['Emploi (entrée)'], self::PROF_STATUS) ?? null)
            ->setContractType($this->findInArray($row['Emploi (entrée)'], self::CONTRACT_TYPE) ?? null)
            ->setResources($this->findInArray($row['Ressources (entrée)'], self::YES_NO) ?? null)
            ->setResourcesAmt((float) $row['Montant ressources (entrée)'])
            ->setUnemplBenefit($row['ARE (entrée)'] == 'Oui' ? 1 : null)
            ->setMinimumIncome($row['RSA (entrée)'] == 'Oui' ? 1 : null)
            ->setFamilyAllowance($row['AF (entrée)'] == 'Oui' ? 1 : null)
            ->setSalary($row['Salaire (entrée)'] == 'Oui' ? 1 : null)
            ->setRessourceOther($row['Autres ressources (entrée)'] ? 1 : null)
            ->setRessourceOtherPrecision($row['Autres ressources (entrée)'])
            ->setComment($row['Commentaire situation à l\'entrée']);

        $this->manager->persist($initEvalPerson);

        return $initEvalPerson;
    }

    protected function createEvalSocialPerson(array $row, EvaluationPerson $evaluationPerson): EvalSocialPerson
    {
        $evalSocialPerson = (new EvalSocialPerson())
        ->setRightSocialSecurity($this->findInArray($row['Couverture maladie'], self::RIGHT_SOCIAL_SECURITY) ?? null)
        ->setSocialSecurity($this->findInArray($row['Couverture maladie'], self::SOCIAL_SECURITY) ?? null)
        ->setFamilyBreakdown($this->findInArray($row['Rupture liens familiaux et amicaux'], self::YES_NO) ?? null)
        ->setFriendshipBreakdown($this->findInArray($row['Rupture liens familiaux et amicaux'], self::YES_NO) ?? null)
        ->setChildWelfareBackground($this->findInArray($row['Parcours institutionnel enfance'], self::YES_NO) ?? null)
        ->setHealthProblem($row['Problématique santé mentale'] == 1 || $row['Problématique santé - Addiction'] == 1 ? 1 : null)
        ->setMentalHealthProblem($this->findInArray($row['Problématique santé mentale'], self::YES_NO_BOOLEAN) ?? null)
        ->setAddictionProblem($this->findInArray($row['Problématique santé - Addiction'], self::YES_NO_BOOLEAN) ?? null)
        ->setCareSupport($this->findInArray($row['Service soin ou acc. à domicile'], self::CARE_SUPPORT) ?? null)
        ->setCareSupportType($this->findInArray($row['Service soin ou acc. à domicile'], self::CARE_SUPPORT_TYPE) ?? null)
        ->setCommentEvalSocialPerson($row['Spécificités autres'])
        ->setEvaluationPerson($evaluationPerson);

        $this->manager->persist($evalSocialPerson);

        return $evalSocialPerson;
    }

    protected function createEvalFamilyPerson(array $row, EvaluationPerson $evaluationPerson): EvalFamilyPerson
    {
        $evalFamilyPerson = (new EvalFamilyPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setUnbornChild($this->findInArray($row['Grossesse'], self::YES_NO) ?? null)
            ->setChildcareSchool($this->findInArray($row['Mode garde'], self::CHILDCARE_SCHOOL) ?? null)
            ->setProtectiveMeasure($this->findInArray($row['Mesure de protection'], self::PROTECTIVE_MEASURE) ?? null)
            ->setProtectiveMeasureType($this->findInArray($row['Mesure de protection'], self::PROTECTIVE_MEASURE_TYPE) ?? null);

        $this->manager->persist($evalFamilyPerson);

        return $evalFamilyPerson;
    }

    protected function createEvalAdmPerson(array $row, EvaluationPerson $evaluationPerson): EvalAdmPerson
    {
        $evalAdmPerson = (new EvalAdmPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setNationality($this->findInArray($row['Nationalité'], self::NATIONALITY) ?? null)
            ->setCountry($row['Pays d\'origine'])
            ->setPaper($this->findInArray($row['Situation administrative'], self::PAPER) ?? null)
            ->setPaperType($this->findInArray($row['Situation administrative'], self::PAPER_TYPE) ?? null)
            ->setAsylumBackground($this->findInArray($row['Parcours asile'], self::YES_NO) ?? null)
            ->setCommentEvalAdmPerson($row['Situation administrative']);

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
        $evalBudgetPerson = (new EvalBudgetPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setResources($this->findInArray($row['Ressources'], self::YES_NO) ?? null)
            ->setResourcesAmt((float) $row['Montant ressources'])
            ->setUnemplBenefit($row['ARE'] == 'Oui' ? 1 : null)
            ->setMinimumIncome($row['RSA'] == 'Oui' ? 1 : null)
            ->setFamilyAllowance($row['AF'] == 'Oui' ? 1 : null)
            ->setSalary($row['Salaire'] == 'Oui' ? 1 : null)
            ->setRessourceOther($row['Autres ressources'] ? 1 : null)
            ->setRessourceOtherPrecision($row['Autres ressources']);

        $this->manager->persist($evalBudgetPerson);

        return $evalBudgetPerson;
    }

    protected function findDevice(array $row): ?Device
    {
        foreach (self::DEVICES as $key => $value) {
            if ($key == $row['Dispositif']) {
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

        if ($row['Rôle'] == ' DP') {
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
        } elseif ($row['Rôle'] == 'Enfant') {
            $this->role = 3;
        } elseif ($row['Rôle'] == 'Conjoint·e') {
            $this->role = 1;
        }
    }

    protected function getStatus(array $row): int
    {
        return $row['Date sortie'] ? 4 : ($row['Date entrée'] ? 2 : 5);
    }

    protected function getStartDate(array $row): ?DateTime
    {
        return $row['Date entrée'] ? new \Datetime($row['Date entrée']) : null;
    }

    protected function getEndDate(array $row): ?DateTime
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
