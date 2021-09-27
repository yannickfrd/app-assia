<?php

namespace App\Service\SiSiao;

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
use App\Entity\People\Person;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class to import evaluation from API SI-SIAO.
 */
class SiSiaoEvaluationImporter extends SiSiaoRequest
{
    use SiSiaoClientTrait;

    protected $manager;
    protected $user;
    protected $flashBag;

    /** @var int ID fiche groupe SI-SIAO */
    protected $id;
    /** @var object */
    protected $ficheGroupe;

    /** @var float */
    protected $resourcesGroupAmt = 0;
    /** @var float */
    protected $chargesGroupAmt = 0;
    /** @var float */
    protected $debtsGroupAmt = 0;
    /** @var float */
    protected $monthlyRepaymentAmt = 0;

    public function __construct(
        HttpClientInterface $client,
        SessionInterface $session,
        EntityManagerInterface $manager,
        Security $security,
        FlashBagInterface $flashBag,
        string $url
    ) {
        parent::__construct($client, $session, $url);

        $this->manager = $manager;
        $this->user = $security->getUser();
        $this->flashBag = $flashBag;
    }

    /**
     * Import a evaluation by ID group.
     */
    public function import(SupportGroup $supportGroup): ?EvaluationGroup
    {
        try {
            return $this->createEvaluation($supportGroup);
        } catch (\Exception $e) {
            $this->flashBag->add('danger', "L'évaluation sociale SI-SIAO n'a pas pu être importée. ".$this->getErrorMessage($e));

            return null;
        }
    }

    protected function createEvaluation(SupportGroup $supportGroup): ?EvaluationGroup
    {
        if ($supportGroup->getEvaluationsGroup()->count() > 0) {
            $this->flashBag->add('warning', 'Une évaluation sociale a déjà été créée pour ce suivi.');

            return null;
        }

        $this->id = $supportGroup->getPeopleGroup()->getSiSiaoId();

        if (!$this->id) {
            $this->flashBag->add('warning', "Il n'y a pas d'ID fiche groupe SI-SIAO saisi pour ce groupe.");

            return null;
        }

        $result = $this->searchById($this->id);

        if (0 === $result->total) {
            $this->flashBag->add('warning', "Il n'y a pas de dossier SI-SIAO correspondant avec la clé '{$this->id}'.");

            return null;
        }

        $this->ficheGroupe = $this->get("fiches/ficheIdentite/{$this->id}");

        $evaluationGroup = $this->createEvaluationGroup($supportGroup);

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $person = $supportPerson->getPerson();
            $personne = $this->matchPersonne($person);
            if ($personne) {
                $evaluationPerson = $this->createEvaluationPerson($evaluationGroup, $supportPerson, $personne);
                $evaluationGroup->addEvaluationPerson($evaluationPerson);
            }
        }

        $this->createEvalBudgetGroup($evaluationGroup);
        $this->createInitEvalGroup($supportGroup, $evaluationGroup);

        $this->manager->flush();

        return $evaluationGroup;
    }

    protected function matchPersonne(Person $person): ?object
    {
        foreach ($this->ficheGroupe->personnes as $personne) {
            if ($person->getSiSiaoId() === $this->getFichePersonneId($personne)
                || ($person->getFirstname() === $personne->prenom && $person->getAge() === $personne->age)
                || $person->getBirthdate()->format('d/m/Y') === $personne->datenaissance) {
                return $personne;
            }
        }

        return null;
    }

    protected function createEvaluationGroup(SupportGroup $supportGroup): EvaluationGroup
    {
        $now = new \DateTime();

        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setDate($now)
            ->setConclusion(null)
            ->setCreatedAt($now)
            ->setUpdatedAt($now)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->manager->persist($evaluationGroup);

        /** @var int */
        $diagSocialId = $this->ficheGroupe->demandeurprincipal->diagnosticSocial->id;
        /** @var object */
        $sitSociale = $this->ficheGroupe->situationsociale;
        /** @var object */
        $sitLogement = $this->get('situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId='.$diagSocialId);
        /** @var object */
        $demandeSiao = $this->get("demandeInsertion/getLastDemandeEnCours?idFiche={$this->id}");

        $this->createEvalSocialGroup($evaluationGroup, $sitSociale, $demandeSiao);
        $this->createEvalFamilyGroup($evaluationGroup, $this->ficheGroupe->situationfamille);
        $this->createEvalHousingGroup($evaluationGroup, $sitSociale, $sitLogement, $demandeSiao);

        return $evaluationGroup;
    }

    protected function createEvalFamilyGroup(EvaluationGroup $evaluationGroup, ?object $sitFamille = null): ?EvalFamilyGroup
    {
        /** @var object */
        $dp = $this->ficheGroupe->demandeurprincipal;

        if (!$sitFamille) {
            return null;
        }

        $evalFamilyGroup = (new EvalFamilyGroup())
            ->setFamlReunification($this->findInArray($sitFamille->regroupementFamilial, SiSiaoItems::FAML_REUNIFICATION))
            ->setNbPeopleReunification($sitFamille->regroupementNombrePersonnes)
            ->setPmiFollowUp($this->findInArray($dp->suiviPMI, SiSiaoItems::YES_NO))
            // ->setChildrenBehind(null)
            ->setCommentEvalFamilyGroup($sitFamille->commentaires)
            ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalFamilyGroup);

        $evaluationGroup->setEvalFamilyGroup($evalFamilyGroup);

        return $evalFamilyGroup;
    }

    protected function createEvalSocialGroup(EvaluationGroup $evaluationGroup, ?object $sitSociale = null, ?object $demandeSiao = null): ?EvalSocialGroup
    {
        if (!$sitSociale) {
            return null;
        }

        $animaux = $this->ficheGroupe->animaux;

        $evalSocialGroup = (new EvalSocialGroup())
            ->setReasonRequest($this->findInArray($demandeSiao ? $demandeSiao->motifDemande :
                $sitSociale->motifdemande, SiSiaoItems::REASON_REQUEST))
            ->setWanderingTime($this->findInArray($sitSociale->dureederrance, SiSiaoItems::WANDERING_TIME))
            ->setCommentEvalSocialGroup(null)
            ->setAnimal(count($animaux) > 0 ? Choices::YES : Choices::NO)
            ->setAnimalType($this->getAnimalType())
            ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalSocialGroup);

        $evaluationGroup->setEvalSocialGroup($evalSocialGroup);

        return $evalSocialGroup;
    }

    protected function getAnimalType(): string
    {
        $animals = [];

        foreach ($this->ficheGroupe->animaux as $animal) {
            $animals[] = $this->findInArray($animal->idAnimal, SiSiaoItems::ANINMAL_TYPE);
        }

        return join(', ', $animals);
    }

    protected function createEvalBudgetGroup(EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setResourcesGroupAmt($this->resourcesGroupAmt)
            ->setChargesGroupAmt($this->chargesGroupAmt)
            ->setDebtsGroupAmt($this->debtsGroupAmt)
            ->setMonthlyRepaymentAmt($this->monthlyRepaymentAmt)
            ->setBudgetBalanceAmt($this->resourcesGroupAmt - $this->chargesGroupAmt - $this->monthlyRepaymentAmt)
            ->setEvaluationGroup($evaluationGroup);

        $this->manager->persist($evalBudgetGroup);

        $evaluationGroup->setEvalBudgetGroup($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    protected function createEvalHousingGroup(EvaluationGroup $evaluationGroup, ?object $sitSociale = null, ?object $sitLogement = null, ?object $demandeSiao = null): EvalHousingGroup
    {
        $evalHousingGroup = (new EvalHousingGroup())
            ->setExpulsionInProgress($this->findInArray($sitSociale->expulsion, SiSiaoItems::YES_NO))
            ->setEvaluationGroup($evaluationGroup);

        if ($sitLogement) {
            $evalHousingGroup
                ->setSocialHousingRequest($this->findInArray($sitLogement->demandeLogementSocial, SiSiaoItems::YES_NO))
                ->setSocialHousingRequestId($sitLogement->numeroUniqueLogementDocial)
                ->setSocialHousingRequestDate($this->convertDate($sitLogement->dateDemandeLogementSocial))
                ->setSocialHousingUpdatedRequestDate($this->convertDate($sitLogement->daterenouvellementLogementSocial))
                ->setCitiesWishes($this->getCitiesWishes($sitLogement->communesDemandeLogementSocial))
                ->setHousingExperience($this->findInArray($sitLogement->experienceLogementAutonome, SiSiaoItems::YES_NO))
                ->setSyplo($this->findInArray($sitLogement->inscriptionSYPLO, SiSiaoItems::SYPLO_STATUS))
                ->setSyploDate($this->convertDate($sitLogement->dateInscriptionSYPLO))
                ->setSyploId($sitLogement->numeroSYPLO)
                ->setDaloAction(SiSiaoItems::YES === $sitLogement->passageCommissionDALO
                    || SiSiaoItems::YES === $sitLogement->passageCommissionDAHO ? Choices::YES : Choices::NO)
                ->setDaloType($this->getDaloType($sitLogement))
                ->setDaloId($sitLogement->numRecoursDALO ?? $sitLogement->numRecoursDAHO)
                ->setDaloRecordDate($this->convertDate($sitLogement->dateDepotDALO ?? $sitLogement->dateDepotDAHO))
                ->setDaloDecisionDate($this->convertDate($sitLogement->dateDecisionDALO ?? $sitLogement->dateDecisionDAHO))
                ->setDaloTribunalAction($this->findInArray($sitLogement->passageCommissionDALO ?? $sitLogement->passageCommissionDAHO, SiSiaoItems::YES_NO))
                ->setDaloTribunalActionDate($this->convertDate($sitLogement->dateRecoursDALO ?? $sitLogement->dateRecoursDAHO))
                ->setHsgActionEligibility(true === $sitLogement->cotisationEntreprise ? Choices::YES : Choices::NO)
                ->setHsgActionRecord(true === $sitLogement->demandeDeposeeEmployeur ? Choices::YES : Choices::NO)
                ->setHsgActionRecordId($sitLogement->organismeCollecteur ? $sitLogement->organismeCollecteur->libelle : null)
                ->setHousingExperience($this->findInArray($sitLogement->experienceLogementAutonome, SiSiaoItems::YES_NO))
                ->setCommentEvalHousing($sitLogement->commentaireSituationLogement);
        }

        if ($demandeSiao) {
            $evalHousingGroup
                ->setHousingStatus($this->findInArray($demandeSiao->situationDemande, SiSiaoItems::HOUSING_STATUS))
                ->setSiaoRequest($demandeSiao ? Choices::YES : Choices::NO)
                ->setSiaoRequestDate($this->convertDate($demandeSiao->dateTransmissionInitialeSiao))
                ->setSiaoUpdatedRequestDate($this->convertDate($demandeSiao->dateTransmissionSiao))
                ->setSiaoRequestDept($this->findInArray($demandeSiao->siao->territoire->codeDepartement, SiSiaoItems::DEPARTMENTS))
                ->setSiaoRecommendation($this->getSiaoRecommendation($demandeSiao->preconisations));
        }

        $sitFamille = $this->ficheGroupe->situationfamille;

        if ($sitFamille) {
            $evalHousingGroup
                ->setDomiciliation($sitFamille->ville ? Choices::YES : Choices::NO)
                ->setDomiciliationAddress($sitFamille->libelleVoie)
                ->setDomiciliationCity($sitFamille->ville)
                ->setDomiciliationZipcode($sitFamille->codepostal);
        }

        $this->manager->persist($evalHousingGroup);

        $evaluationGroup->setEvalHousingGroup($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function getCitiesWishes(?array $communes): ?string
    {
        $citiesWishes = '';

        foreach ($communes as $commune) {
            $citiesWishes .= $commune->nom.', ';
        }

        return $citiesWishes;
    }

    protected function getSiaoRecommendation(?array $preconisations): ?int
    {
        foreach ($preconisations as $preco) {
            return $this->findInArray($preco->typeEtablissementUn, SiSiaoItems::TYPES_ETABLISSEMENT_UN);
        }

        return null;
    }

    protected function getDaloType(object $sitLogement): ?int
    {
        if (true === $sitLogement->daloRequalifieDAHO) {
            return 3; // DALO requalifié hébergement
        }
        if (SiSiaoItems::YES === $sitLogement->passageCommissionDALO) {
            return 2; // Logement
        }
        if (SiSiaoItems::YES === $sitLogement->passageCommissionDAHO) {
            return 1; // Hébergement
        }

        return null;
    }

    protected function createInitEvalGroup(SupportGroup $supportGroup, EvaluationGroup $evaluationGroup): InitEvalGroup
    {
        $evalHousingGroup = $evaluationGroup->getEvalHousingGroup();
        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();

        $initEvalGroup = (new InitEvalGroup())
            ->setHousingStatus($evalHousingGroup->getHousingStatus())
            ->setSiaoRequest($evalHousingGroup->getSiaoRequest())
            ->setSocialHousingRequest($evalHousingGroup->getSocialHousingRequest())
            ->setResourcesGroupAmt($evalBudgetGroup->getResourcesGroupAmt())
            ->setDebtsGroupAmt($evalBudgetGroup->getDebtsGroupAmt())
            ->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        $evaluationGroup->setInitEvalGroup($initEvalGroup);

        return $initEvalGroup;
    }

    protected function createEvaluationPerson(EvaluationGroup $evaluationGroup, SupportPerson $supportPerson, object $personne): EvaluationPerson
    {
        $evaluationPerson = new EvaluationPerson();

        $evaluationPerson->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson)
            ->setCreatedBy($this->user)
            ->setUpdatedBy($this->user);

        $this->manager->persist($evaluationPerson);

        $this->createEvalSocialPerson($evaluationPerson, $personne);
        $this->createEvalAdmPerson($evaluationPerson, $personne);
        $this->createEvalFamilyPerson($evaluationPerson, $personne);

        $diagSocialId = $personne->diagnosticSocial ? $personne->diagnosticSocial->id : null;

        if ($diagSocialId) {
            $diagSocial = $this->get("diagnosticSocials/{$diagSocialId}");
            $this->createEvalProfPerson($evaluationPerson, $personne, $diagSocial);
            $this->createEvalBudgetPerson($evaluationPerson, $personne, $diagSocial);
        }

        $this->createInitEvalPerson($supportPerson, $evaluationPerson);

        return $evaluationPerson;
    }

    protected function createEvalSocialPerson(EvaluationPerson $evaluationPerson, object $personne): ?EvalSocialPerson
    {
        /** @var object */
        $sitAdm = $personne->situationadministrative;

        $evalSocialPerson = (new EvalSocialPerson())
            ->setRightSocialSecurity($this->findInArray($sitAdm->droisecuritesociale, SiSiaoItems::YES_NO))
            ->setSocialSecurity($this->getSocialSecurity($sitAdm->typeDroitSecuriteSociale))
            ->setSocialSecurityOffice($sitAdm->nomcaisse)
            // ->setChildWelfareBackground(null)
            // ->setHealthProblem(null)
            // ->setMentalHealthProblem(null)
            // ->setAddictionProblem(null)
            // ->setHomeCareSupport(null)
            // ->setHomeCareSupportType(null)
            // ->setCommentEvalSocialPerson(null)
            ->setEvaluationPerson($evaluationPerson);

        if ($evaluationPerson->getSupportPerson()->getHead()) {
            /** @var object */
            $sitSocial = $this->ficheGroupe->situationsociale;

            $evalSocialPerson
                ->setMedicalFollowUp($this->findInArray($sitSocial->suivimedical, SiSiaoItems::YES_NO))
                ->setReducedMobility($this->findInArray($sitSocial->problemeMobilite, SiSiaoItems::YES_NO))
                ->setWheelchair($this->findInArray($sitSocial->fauteuilRoulant, SiSiaoItems::YES_NO))
                ->setViolenceVictim($this->ficheGroupe->victimeviolence)
                ->setDomViolenceVictim($this->findInArray($this->ficheGroupe->typevictime, SiSiaoItems::DOM_VIOLENCE_VICTIM))
                ->setAseFollowUp($this->findInArray($sitSocial->priseEnChargeASE, SiSiaoItems::YES_NO))
                ->setAseComment($this->findInArray($sitSocial->etatAse, SiSiaoItems::ASE_STATUS).'. '.
                    (null !== $sitSocial->departementAse ? $sitSocial->departementAse->libelle : null));
        }

        $this->manager->persist($evalSocialPerson);

        $evaluationPerson->setEvalSocialPerson($evalSocialPerson);

        return $evalSocialPerson;
    }

    protected function getSocialSecurity(?array $typeDroitSecuriteSociale)
    {
        foreach ($typeDroitSecuriteSociale as $value) {
            return $this->findInArray($value, SiSiaoItems::SOCIAL_SECURITY);
        }

        return null;
    }

    protected function createEvalFamilyPerson(EvaluationPerson $evaluationPerson, object $personne): ?EvalFamilyPerson
    {
        $evalFamilyPerson = (new EvalFamilyPerson())
            ->setMaritalStatus($this->findInArray($personne->situation, SiSiaoItems::MARITAL_STATUS))
            ->setUnbornChild($this->findInArray($personne->grossesse, SiSiaoItems::YES_NO))
            ->setExpDateChildbirth($this->convertDate($personne->dateTerme))
            ->setPregnancyType($this->findInArray($personne->typeGrossesse, SiSiaoItems::PREGNANCY_TYPE))
            ->setChildcareSchoolType(null)
            ->setProtectiveMeasure(null)
            ->setProtectiveMeasureType(null)
            ->setEvaluationPerson($evaluationPerson);

        $this->manager->persist($evalFamilyPerson);

        $evaluationPerson->setEvalFamilyPerson($evalFamilyPerson);

        return $evalFamilyPerson;
    }

    protected function createEvalAdmPerson(EvaluationPerson $evaluationPerson, object $personne): ?EvalAdmPerson
    {
        /** @var object */
        $sitAdm = $personne->situationadministrative;

        if (!$sitAdm) {
            return null;
        }

        $evalAdmPerson = (new EvalAdmPerson())
            ->setEvaluationPerson($evaluationPerson)
            ->setNationality($this->findInArray($sitAdm->nationalite, SiSiaoItems::NATIONALITY))
            ->setCountry($sitAdm->pays ? $sitAdm->pays->libelle : null)
            ->setArrivalDate($this->convertDate($sitAdm->dateEntreeFrance))
            ->setEndValidPermitDate($this->convertDate($sitAdm->dateFinValiditeTitreSejour))
            ->setRenewalPermitDate($this->convertDate($sitAdm->dateRenouvellementTitreSejour))
            ->setNbRenewals($sitAdm->nombreRenouvellementTitreSejour)
            ->setPaper($this->findInArray($sitAdm->papieridentite, SiSiaoItems::YES_NO))
            ->setPaperType($this->getPaperType($sitAdm))
            ->setAsylumBackground($this->findInArray($sitAdm->droitsejour, SiSiaoItems::ASYLUM_BACKGROUND))
            ->setWorkRight($sitAdm->droitTravaillerTitreSejour)
            ->setAgdrefId($sitAdm->numeroAgdref)
            ->setCommentEvalAdmPerson($sitAdm->commentaires);

        $this->manager->persist($evalAdmPerson);

        $evaluationPerson->setEvalAdmPerson($evalAdmPerson);

        return $evalAdmPerson;
    }

    protected function getPaperType(object $sitAdm): ?int
    {
        if ($sitAdm->typepapieridentite) {
            return $this->findInArray($sitAdm->typepapieridentite, SiSiaoItems::PAPER_TYPE);
        }

        return $this->findInArray($sitAdm->droitsejour, SiSiaoItems::PAPER_TYPE_ASYLUM_STATUS);
    }

    protected function createEvalProfPerson(EvaluationPerson $evaluationPerson, object $personne, object $diagSocial): ?EvalProfPerson
    {
        if ($personne->age < 16) {
            return null;
        }

        $evalProfPerson = (new EvalProfPerson())
            ->setProfStatus($this->getProfStatus($diagSocial))
            ->setContractType($this->findInArray($diagSocial->typeContrat, SiSiaoItems::CONTRACT_TYPE))
            ->setContractStartDate($this->convertDate($diagSocial->dateDebutContrat))
            ->setContractEndDate($this->convertDate($diagSocial->dateFinContrat))
            ->setNbWorkingHours($diagSocial->nombreHeures)
            ->setWorkingHours($diagSocial->horaireTravail)
            ->setJobType($diagSocial->posteOccupe)
            ->setWorkPlace($diagSocial->communeEmploi.
                ($diagSocial->departementEmploi ? ' ('.$diagSocial->departementEmploi.')' : null))
            ->setRqth($this->findInArray($diagSocial->rqth, SiSiaoItems::YES_NO))
            ->setTransportMeansType(true === $diagSocial->moyenLocomotion ? 1 : null)
            ->setCommentEvalProf($diagSocial->commentaires)
            ->setEvaluationPerson($evaluationPerson);

        $this->manager->persist($evalProfPerson);

        $evaluationPerson->setEvalProfPerson($evalProfPerson);

        return $evalProfPerson;
    }

    protected function getProfStatus(object $diagSocial): ?int
    {
        if (SiSiaoItems::YES === $diagSocial->enEmploi) {
            return 8;
        }
        if (SiSiaoItems::YES === $diagSocial->enFormation) {
            return 3;
        }
        if (SiSiaoItems::YES === $diagSocial->etudiant) {
            return 5;
        }
        if (SiSiaoItems::YES === $diagSocial->demandeur) {
            return 2;
        }
        if (SiSiaoItems::YES === $diagSocial->retraite) {
            return 7;
        }

        return Choices::NO_INFORMATION;
    }

    protected function createEvalBudgetPerson(EvaluationPerson $evaluationPerson, object $personne, object $diagSocial): ?EvalBudgetPerson
    {
        if ($personne->age < 16) {
            return null;
        }

        $evalBudgetPerson = (new EvalBudgetPerson())
            ->setOverIndebtRecord($this->findInArray($diagSocial->dossierSurendettement, SiSiaoItems::YES_NO))
            ->setOverIndebtRecordDate($this->convertDate($diagSocial->dateDepotDossierSurendettement))
            ->setSettlementPlan($this->findInArray($diagSocial->apurementDette, SiSiaoItems::YES_NO_BOOL))
            ->setMoratorium($this->findInArray($diagSocial->moratoire, SiSiaoItems::YES_NO_BOOL))
            ->setChargeComment($diagSocial->commentaireCharge)
            ->setMonthlyRepaymentAmt($diagSocial->remboursementDettes)
            ->setDebtComment($diagSocial->commentairesSituationBudgetaire)
            ->setCommentEvalBudget($diagSocial->commentaireRessource."\n"
                .$diagSocial->commentaireCharge."\n"
                .$diagSocial->commentairesSituationBudgetaire."\n")
           ->setEvaluationPerson($evaluationPerson);

        $this->setResources($evalBudgetPerson, $diagSocial);
        $this->setCharges($evalBudgetPerson, $diagSocial);
        $this->setDebts($evalBudgetPerson, $diagSocial);
        $this->monthlyRepaymentAmt += $diagSocial->remboursementDettes;

        $this->manager->persist($evalBudgetPerson);

        $evaluationPerson->setEvalBudgetPerson($evalBudgetPerson);

        return $evalBudgetPerson;
    }

    protected function setResources(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $ressources = $this->get("ressourcePersonnes/diagnosticSocial/{$diagSocial->id}");
        $count = 0;
        $sumAmt = 0;

        foreach ($ressources as $ressource) {
            ++$count;
            $resourceValue = $this->findInArray($ressource->typeRessource, SiSiaoItems::TYPE_RESOURCES);
            $amt = $ressource->montant;
            $sumAmt += $amt;
            $setMethod = 'set'.ucfirst($resourceValue);
            $setAmtMethod = $setMethod.'Amt';

            if (method_exists($evalBudgetPerson, $setMethod)) {
                $evalBudgetPerson->$setMethod(1);
            }
            if (method_exists($evalBudgetPerson, $setAmtMethod)) {
                $evalBudgetPerson->$setAmtMethod($amt);
            }
            if (1000 === $ressource->typeRessource->id) {
                $evalBudgetPerson->setRessourceOtherPrecision($ressource->commentaire);
            }
        }

        $evalBudgetPerson = $this->setEmptyEvalBudgetItems($evalBudgetPerson, EvalBudgetPerson::RESOURCES_TYPE);

        $evalBudgetPerson
            ->setResources($count > 0 ? Choices::YES : (true === $diagSocial->sansRessource ? Choices::NO : null))
            ->setResourcesAmt($sumAmt);

        $this->resourcesGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    protected function setCharges(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $charges = $this->get("chargePersonnes/diagnosticSocial/{$diagSocial->id}");
        $count = 0;
        $sumAmt = 0;

        foreach ($charges as $charge) {
            ++$count;
            $chargeValue = $this->findInArray($charge->typeCharge, SiSiaoItems::TYPE_CHARGES);
            $amt = $charge->montant;
            $sumAmt += $amt;
            $setMethod = 'set'.ucfirst($chargeValue);
            $setAmtMethod = $setMethod.'Amt';

            if (method_exists($evalBudgetPerson, $setMethod)) {
                $evalBudgetPerson->$setMethod(1);
            }
            if (method_exists($evalBudgetPerson, $setAmtMethod)) {
                $evalBudgetPerson->$setAmtMethod($amt);
            }
            if (1000 === $charge->typeCharge->id) {
                $evalBudgetPerson->setChargeOtherPrecision($charge->commentaire);
            }
        }

        $evalBudgetPerson = $this->setEmptyEvalBudgetItems($evalBudgetPerson, EvalBudgetPerson::CHARGES_TYPE);

        $evalBudgetPerson
            ->setCharges($count > 0 ? Choices::YES : (true === $diagSocial->sansCharge ? Choices::NO : null))
            ->setChargesAmt($sumAmt);

        $this->chargesGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    protected function setDebts(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $dettes = $this->get("dettePersonnes/diagnosticSocial/{$diagSocial->id}");
        $count = 0;
        $sumAmt = 0;

        foreach ($dettes as $dette) {
            ++$count;
            $debtValue = $this->findInArray($dette->typeDette, SiSiaoItems::TYPE_DEBTS);
            $amt = $dette->montant;
            $sumAmt += $amt;
            $setMethod = 'set'.ucfirst($debtValue);

            if (method_exists($evalBudgetPerson, $setMethod)) {
                $evalBudgetPerson->$setMethod(1);
            }
            if (1000 === $dette->typeDette->id) {
                $evalBudgetPerson->setDebtOtherPrecision($dette->commentaire);
            }
        }

        $evalBudgetPerson = $this->setEmptyEvalBudgetItems($evalBudgetPerson, EvalBudgetPerson::DEBTS_TYPE);

        $evalBudgetPerson->setDebts($count > 0 ? Choices::YES : (true === $diagSocial->sansDette ? Choices::NO : null))
            ->setDebtsAmt($sumAmt);

        $this->debtsGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    /**
     * Set the empty resources, charges and debts values to 0.
     */
    protected function setEmptyEvalBudgetItems(EvalBudgetPerson $evalBudgetPerson, array $values): EvalBudgetPerson
    {
        foreach ($values as $key => $value) {
            $getMethod = 'get'.ucfirst($key);
            $setMethod = 'set'.ucfirst($key);
            if (method_exists($evalBudgetPerson, $getMethod) && null === $evalBudgetPerson->$getMethod()) {
                $evalBudgetPerson->$setMethod(0);
            }
        }

        return $evalBudgetPerson;
    }

    protected function createInitEvalPerson(SupportPerson $supportPerson, EvaluationPerson $evaluationPerson): ?InitEvalPerson
    {
        $evalAdmPerson = $evaluationPerson->getEvalAdmPerson();
        $evalSocialPerson = $evaluationPerson->getEvalSocialPerson();
        $evalProfPerson = $evaluationPerson->getEvalProfPerson();
        $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();

        $initEvalPerson = (new InitEvalPerson())
            ->setPaper($evalAdmPerson->getPaper())
            ->setPaperType($evalAdmPerson->getPaperType())
            ->setRightSocialSecurity($evalSocialPerson->getRightSocialSecurity())
            ->setSocialSecurity($evalSocialPerson->getSocialSecurity())
            ->setFamilyBreakdown($evalSocialPerson->getFamilyBreakdown())
            ->setFriendshipBreakdown($evalSocialPerson->getFriendshipBreakdown())
            ->setSupportPerson($supportPerson);

        if ($evalProfPerson) {
            $initEvalPerson
                ->setProfStatus($evalProfPerson->getProfStatus())
                ->setContractType($evalProfPerson->getContractType());
        }

        if ($evalBudgetPerson) {
            $initEvalPerson
                ->setResources($evalBudgetPerson->getResources())
                ->setResourcesAmt($evalBudgetPerson->getResourcesAmt())
                ->setRessourceOtherPrecision($evalBudgetPerson->getRessourceOtherPrecision())
                ->setDebts($evalBudgetPerson->getDebts())
                ->setDebtsAmt($evalBudgetPerson->getDebtsAmt());

            $this->setResourcesInit($evalBudgetPerson, $initEvalPerson);
        }

        $this->manager->persist($initEvalPerson);

        $evaluationPerson->setInitEvalPerson($initEvalPerson);

        return $initEvalPerson;
    }

    /**
     * Dupplique les ressources de la situation budgétaire dans la situation initiale.
     */
    protected function setResourcesInit(EvalBudgetPerson $evalBudgetPerson, InitEvalPerson $initEvalPerson): InitEvalPerson
    {
        foreach (EvalBudgetPerson::RESOURCES_TYPE as $key => $value) {
            $getMethod = 'get'.ucfirst($key);
            $setMethod = 'set'.ucfirst($key);
            if (Choices::YES === $evalBudgetPerson->$getMethod()) {
                $getAmtMethod = $getMethod.'Amt';
                if (method_exists($initEvalPerson, $getMethod)) {
                    $initEvalPerson->$setMethod($evalBudgetPerson->$getMethod());
                }
                if (method_exists($initEvalPerson, $getAmtMethod)) {
                    $setAmtMethod = 'set'.ucfirst($key).'Amt';
                    $initEvalPerson->$setAmtMethod($evalBudgetPerson->$getAmtMethod());
                }
            }
        }

        return $initEvalPerson;
    }
}
