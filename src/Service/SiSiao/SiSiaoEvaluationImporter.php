<?php

namespace App\Service\SiSiao;

use App\Entity\Evaluation\AbstractFinance;
use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetCharge;
use App\Entity\Evaluation\EvalBudgetDebt;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalBudgetResource;
use App\Entity\Evaluation\EvalFamilyGroup;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalInitResource;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialGroup;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\EvalInitGroup;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\Resource as EvaResource;
use App\Entity\People\Person;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use App\Notification\ExceptionNotification;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class to import evaluation from API SI-SIAO.
 */
class SiSiaoEvaluationImporter extends SiSiaoClient
{
    protected $em;
    protected $user;
    protected $flashBag;
    protected $exceptionNotification;

    /** @var int ID fiche groupe SI-SIAO */
    protected $id;
    /** @var object */
    protected $ficheGroupe;

    /** @var EvaluationGroup */
    protected $evaluationGroup;

    /** @var float */
    protected $resourcesGroupAmt = 0;
    /** @var float */
    protected $chargesGroupAmt = 0;
    /** @var float */
    protected $debtsGroupAmt = 0;

    public function __construct(
        HttpClientInterface $client,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        Security $security,
        FlashBagInterface $flashBag,
        ExceptionNotification $exceptionNotification,
        string $url
    ) {
        parent::__construct($client, $requestStack, $url);

        $this->em = $em;
        $this->user = $security->getUser();
        $this->flashBag = $flashBag;
        $this->exceptionNotification = $exceptionNotification;
    }

    /**
     * Import a evaluation by ID group.
     */
    public function import(SupportGroup $supportGroup): ?EvaluationGroup
    {
        try {
            return $this->tryImportEvaluation($supportGroup);
        } catch (\Exception $e) {
            $this->exceptionNotification->sendException($e);

            $this->flashBag->add('danger', $this->getErrorMessage($e).
                "L'évaluation sociale SI-SIAO n'a pas pu être importée.");

            return null;
        }
    }

    protected function tryImportEvaluation(SupportGroup $supportGroup): ?EvaluationGroup
    {
        if (false === $this->isConnected()) {
            $this->flashBag->add('danger', "L'évaluation sociale SI-SIAO n'a pas pu être importée, 
                car vous n'êtes pas ou plus connecté·e au SI-SIAO.");

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

        $this->evaluationGroup = $supportGroup->getEvaluationsGroup()->first();

        $this->ficheGroupe = $this->get("fiches/ficheIdentite/{$this->id}");

        $evaluationGroup = $this->createOrEditEvaluationGroup($supportGroup);

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $personne = $this->matchPerson($supportPerson->getPerson());
            if ($personne) {
                $this->createOrEditEvaluationPerson($evaluationGroup, $supportPerson, $personne);
            }
        }

        $this->createOrEditEvalBudgetGroup($evaluationGroup);

        if (!$evaluationGroup->getEvalInitGroup()) {
            $this->createEvalInitGroup($supportGroup, $evaluationGroup);
        }

        $this->em->flush();

        $this->flashBag->add('success', "L'évaluation sociale a été ".($this->evaluationGroup ? 'actualisée.' : 'importée.'));

        return $evaluationGroup;
    }

    protected function matchPerson(Person $person): ?object
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

    protected function createOrEditEvaluationGroup(SupportGroup $supportGroup): EvaluationGroup
    {
        if (!$evaluationGroup = $this->evaluationGroup) {
            $evaluationGroup = (new EvaluationGroup())
                ->setSupportGroup($supportGroup)
                ->setDate(new \DateTime())
            ;

            $this->em->persist($evaluationGroup);
        }

        /** @var object */
        $sitSociale = $this->ficheGroupe->situationsociale;
        /** @var object */
        $demandeSiao = $this->get("demandeInsertion/getLastDemandeEnCours?idFiche={$this->id}");

        $this->createOrEditEvalSocialGroup($evaluationGroup, $sitSociale, $demandeSiao);
        $this->createOrEditEvalFamilyGroup($evaluationGroup);
        $this->createOrEditEvalHousingGroup($evaluationGroup, $sitSociale, $demandeSiao);
        $this->editHotelSupport($supportGroup->getHotelSupport());

        return $evaluationGroup;
    }

    protected function editHotelSupport(?HotelSupport $hotelSupport = null): ?HotelSupport
    {
        if (!$hotelSupport) {
            return null;
        }

        $hotelSupport->setRosalieId($this->ficheGroupe->rosalieFamilleId);

        return $hotelSupport;
    }

    protected function createOrEditEvalFamilyGroup(EvaluationGroup $evaluationGroup): ?EvalFamilyGroup
    {
        if (!$sitFamille = $this->ficheGroupe->situationfamille) {
            return null;
        }

        if (!$evalFamilyGroup = $evaluationGroup->getEvalFamilyGroup()) {
            $evalFamilyGroup = (new EvalFamilyGroup())
                // ->setChildrenBehind(null)
                ->setCommentEvalFamilyGroup($sitFamille->commentaires)
                ->setEvaluationGroup($evaluationGroup)
            ;

            $this->em->persist($evalFamilyGroup);
        }

        $evalFamilyGroup
            ->setFamlReunification($this->findInArray($sitFamille->regroupementFamilial, SiSiaoItems::FAML_REUNIFICATION))
            ->setNbPeopleReunification($sitFamille->regroupementNombrePersonnes)
        ;

        $evaluationGroup->setEvalFamilyGroup($evalFamilyGroup);

        return $evalFamilyGroup;
    }

    /**
     * @param object|array|null $demandeSiao
     */
    protected function createOrEditEvalSocialGroup(EvaluationGroup $evaluationGroup, ?object $sitSociale = null, $demandeSiao = null): ?EvalSocialGroup
    {
        if (!$sitSociale) {
            return null;
        }

        $animaux = $this->ficheGroupe->animaux;

        if (!$evalSocialGroup = $evaluationGroup->getEvalSocialGroup()) {
            $evalSocialGroup = (new EvalSocialGroup())
                // ->setCommentEvalSocialGroup(null)
                ->setEvaluationGroup($evaluationGroup);

            $this->em->persist($evalSocialGroup);
        }

        $evalSocialGroup
            ->setReasonRequest($this->findInArray($demandeSiao && is_object($demandeSiao) ? $demandeSiao->motifDemande :
                $sitSociale->motifdemande, SiSiaoItems::REASON_REQUEST))
            // ->setWanderingTime($this->findInArray($sitSociale->dureederrance, SiSiaoItems::WANDERING_TIME))
            ->setAnimal(count($animaux) > 0 ? Choices::YES : Choices::NO)
            ->setAnimalType($this->getAnimalType())
        ;

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

    protected function createOrEditEvalBudgetGroup(EvaluationGroup $evaluationGroup): EvalBudgetGroup
    {
        if (!$evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup()) {
            $evalBudgetGroup = (new EvalBudgetGroup())->setEvaluationGroup($evaluationGroup);

            $this->em->persist($evalBudgetGroup);
        }

        $evalBudgetGroup
            ->setResourcesGroupAmt($this->resourcesGroupAmt)
            ->setChargesGroupAmt($this->chargesGroupAmt)
            ->setDebtsGroupAmt($this->debtsGroupAmt)
            ->setBudgetBalanceAmt($this->resourcesGroupAmt - $this->chargesGroupAmt)
        ;

        $evaluationGroup->setEvalBudgetGroup($evalBudgetGroup);

        return $evalBudgetGroup;
    }

    /**
     * @param object|array|null $demandeSiao
     */
    protected function createOrEditEvalHousingGroup(EvaluationGroup $evaluationGroup,
        ?object $sitSociale = null, $demandeSiao = null): EvalHousingGroup
    {
        /** @var object */
        $sitLogement = $this->get("situationParRapportAuLogement/getByFicheId?ficheId={$this->id}");
        if (!$evalHousingGroup = $evaluationGroup->getEvalHousingGroup()) {
            $evalHousingGroup = (new EvalHousingGroup())
                ->setCommentEvalHousing($sitLogement ? $sitLogement->commentaireSituationLogement : null)
                ->setEvaluationGroup($evaluationGroup)
            ;

            $this->em->persist($evalHousingGroup);
        }

        $evalHousingGroup->setExpulsionInProgress($this->findInArray($sitSociale->expulsion, SiSiaoItems::YES_NO));

        if ($sitLogement) {
            $evalHousingGroup
                ->setSiaoRequest($demandeSiao ? Choices::YES : Choices::NO)
                ->setSocialHousingRequest($this->findInArray($sitLogement->demandeLogementSocial, SiSiaoItems::YES_NO))
                ->setSocialHousingRequestId($this->getSocialHousingRequestId($sitLogement->numerosUniqueLogementSocial))
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
            ;
        }

        if ($demandeSiao && is_object($demandeSiao)) {
            $evalHousingGroup
                ->setHousingStatus($this->findInArray($demandeSiao->situationDemande, SiSiaoItems::HOUSING_STATUS))
                ->setSiaoRequestDate($this->convertDate($demandeSiao->dateTransmissionInitialeSiao))
                ->setSiaoUpdatedRequestDate($this->convertDate($demandeSiao->dateTransmissionSiao))
                ->setSiaoRequestDept($this->findInArray($demandeSiao->siao->territoire->codeDepartement, SiSiaoItems::DEPARTMENTS))
                ->setSiaoRecommendation($this->getSiaoRecommendation($demandeSiao->preconisations))
            ;
        }

        $sitFamille = $this->ficheGroupe->situationfamille;

        if ($sitFamille) {
            $evalHousingGroup
                ->setDomiciliation($sitFamille->ville ? Choices::YES : Choices::NO)
                ->setDomiciliationAddress($sitFamille->libelleVoie)
                ->setDomiciliationCity($sitFamille->ville)
                ->setDomiciliationZipcode($sitFamille->codepostal)
            ;
        }

        $this->em->persist($evalHousingGroup);

        $evaluationGroup->setEvalHousingGroup($evalHousingGroup);

        return $evalHousingGroup;
    }

    protected function getSocialHousingRequestId(array $numerosUniqueLogementSocial): ?string
    {
        foreach ($numerosUniqueLogementSocial as $numeroUniqueLogementSocial) {
            return $numeroUniqueLogementSocial->numeroUnique;
        }

        return null;
    }

    protected function getCitiesWishes(?array $communes): ?string
    {
        $citiesWishes = [];

        foreach ($communes as $commune) {
            $citiesWishes[] = $commune->nom;
        }

        return join(', ', $citiesWishes);
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

    protected function createEvalInitGroup(SupportGroup $supportGroup, EvaluationGroup $evaluationGroup): EvalInitGroup
    {
        $evalHousingGroup = $evaluationGroup->getEvalHousingGroup();
        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();

        $evalInitGroup = (new EvalInitGroup())
            ->setHousingStatus($evalHousingGroup->getHousingStatus())
            ->setSiaoRequest($evalHousingGroup->getSiaoRequest())
            ->setSocialHousingRequest($evalHousingGroup->getSocialHousingRequest())
            ->setResourcesGroupAmt($evalBudgetGroup->getResourcesGroupAmt())
            ->setDebtsGroupAmt($evalBudgetGroup->getDebtsGroupAmt())
            ->setSupportGroup($supportGroup)
        ;

        $this->em->persist($evalInitGroup);

        $evaluationGroup->setEvalInitGroup($evalInitGroup);

        return $evalInitGroup;
    }

    protected function createOrEditEvaluationPerson(EvaluationGroup $evaluationGroup, SupportPerson $supportPerson, object $personne): EvaluationPerson
    {
        if (!$evaluationPerson = $supportPerson->getEvaluations()->first()) {
            $evaluationPerson = (new EvaluationPerson())
                ->setEvaluationGroup($evaluationGroup)
                ->setSupportPerson($supportPerson)
            ;

            $evaluationGroup->addEvaluationPerson($evaluationPerson);

            $this->em->persist($evaluationPerson);
        }

        $this->createOrEditEvalSocialPerson($evaluationPerson, $personne);
        $this->createOrEditEvalAdmPerson($evaluationPerson, $personne);
        $this->createOrEditEvalFamilyPerson($evaluationPerson, $personne);

        $diagSocialId = $personne->diagnosticSocial ? $personne->diagnosticSocial->id : null;

        if ($diagSocialId) {
            $diagSocial = $this->get("diagnosticSocials/{$diagSocialId}");
            $this->createOrEditEvalProfPerson($evaluationPerson, $personne, $diagSocial);
            $this->createOrEditEvalBudgetPerson($evaluationPerson, $personne, $diagSocial);
        }

        if (!$evaluationPerson->getEvalInitPerson()) {
            $this->createEvalInitPerson($supportPerson, $evaluationPerson);
        }

        return $evaluationPerson;
    }

    protected function createOrEditEvalSocialPerson(EvaluationPerson $evaluationPerson, object $personne): ?EvalSocialPerson
    {
        /** @var object */
        $sitAdm = $personne->situationadministrative;

        if (!$evalSocialPerson = $evaluationPerson->getEvalSocialPerson()) {
            $evalSocialPerson = (new EvalSocialPerson())
                // ->setCommentEvalSocialPerson(null)
                ->setEvaluationPerson($evaluationPerson)
            ;

            $this->em->persist($evalSocialPerson);
        }

        $evalSocialPerson
            // ->setChildWelfareBackground(null)
            // ->setMentalHealthProblem(null)
            // ->setAddictionProblem(null)
            // ->setHomeCareSupport(null)
            // ->setHomeCareSupportType(null)
            ->setRightSocialSecurity($this->findInArray($sitAdm->droisecuritesociale, SiSiaoItems::YES_NO))
            ->setSocialSecurity($this->getSocialSecurity($sitAdm->typeDroitSecuriteSociale))
            ->setSocialSecurityOffice($sitAdm->nomcaisse)
            ->setAseFollowUp($this->findInArray($personne->priseEnChargeASE, SiSiaoItems::YES_NO))
            ->setAseComment($this->findInArray($personne->etatAse, SiSiaoItems::ASE_STATUS).'. '.
                (null !== $personne->departementAse ? $personne->departementAse->libelle : null))
        ;

        if ($evaluationPerson->getSupportPerson()->getHead()) {
            /** @var object */
            $sitSocial = $this->ficheGroupe->situationsociale;

            $evalSocialPerson
                ->setMedicalFollowUp($this->findInArray($sitSocial->suivimedical, SiSiaoItems::YES_NO))
                ->setHealthProblem(in_array(SiSiaoItems::YES, [$sitSocial->problemeMobilite, $sitSocial->fauteuilRoulant]) ?
                    EvaluationChoices::YES : EvaluationChoices::NO)
                ->setReducedMobility($this->findInArray($sitSocial->problemeMobilite, SiSiaoItems::YES_NO_STRING_TO_BOOL))
                ->setWheelchair($this->findInArray($sitSocial->fauteuilRoulant, SiSiaoItems::YES_NO_STRING_TO_BOOL))
                ->setViolenceVictim($this->ficheGroupe->victimeviolence)
                ->setDomViolenceVictim($this->findInArray($this->ficheGroupe->typevictime, SiSiaoItems::DOM_VIOLENCE_VICTIM))
            ;
        }

        $evaluationPerson->setEvalSocialPerson($evalSocialPerson);

        return $evalSocialPerson;
    }

    protected function getSocialSecurity(?array $typeDroitSecuriteSociale): ?int
    {
        foreach ($typeDroitSecuriteSociale as $value) {
            return $this->findInArray($value, SiSiaoItems::SOCIAL_SECURITY);
        }

        return null;
    }

    protected function createOrEditEvalFamilyPerson(EvaluationPerson $evaluationPerson, object $personne): ?EvalFamilyPerson
    {
        if (!$evalFamilyPerson = $evaluationPerson->getEvalFamilyPerson()) {
            $evalFamilyPerson = (new EvalFamilyPerson())
                ->setEvaluationPerson($evaluationPerson);

            $this->em->persist($evalFamilyPerson);
        }

        $evalFamilyPerson
            ->setMaritalStatus($this->findInArray($personne->situation, SiSiaoItems::MARITAL_STATUS))
            ->setUnbornChild($this->findInArray($personne->grossesse, SiSiaoItems::YES_NO))
            ->setExpDateChildbirth($this->convertDate($personne->dateTerme))
            ->setPregnancyType($this->findInArray($personne->typeGrossesse, SiSiaoItems::PREGNANCY_TYPE))
            // ->setChildcareSchoolType(null)
            // ->setProtectiveMeasure(null)
            // ->setProtectiveMeasureType(null)
            ->setPmiFollowUp($this->findInArray($personne->suiviPMI, SiSiaoItems::YES_NO))
        ;

        $evaluationPerson->setEvalFamilyPerson($evalFamilyPerson);

        return $evalFamilyPerson;
    }

    protected function createOrEditEvalAdmPerson(EvaluationPerson $evaluationPerson, object $personne): ?EvalAdmPerson
    {
        /** @var object */
        $sitAdm = $personne->situationadministrative;

        if (!$sitAdm) {
            return null;
        }

        if (!$evalAdmPerson = $evaluationPerson->getEvalAdmPerson()) {
            $evalAdmPerson = (new EvalAdmPerson())
                ->setEvaluationPerson($evaluationPerson)
                ->setCommentEvalAdmPerson($sitAdm->commentaires);

            $this->em->persist($evalAdmPerson);
        }

        $evalAdmPerson
            ->setNationality($this->findInArray($sitAdm->nationalite, SiSiaoItems::NATIONALITY))
            // ->setCountry($sitAdm->pays ? $sitAdm->pays->libelle : null)
            ->setPaper($this->findInArray($sitAdm->droitsejour, SiSiaoItems::PAPER))
            ->setPaperType($this->findInArray($sitAdm->droitsejour, SiSiaoItems::PAPER_TYPE))
            ->setArrivalDate($this->convertDate($sitAdm->dateEntreeFrance))
            ->setEndValidPermitDate($this->convertDate($sitAdm->dateFinValiditeTitreSejour))
            ->setRenewalPermitDate($this->convertDate($sitAdm->dateRenouvellementTitreSejour))
            ->setNbRenewals($sitAdm->nombreRenouvellementTitreSejour)
            ->setWorkRight($sitAdm->droitTravaillerTitreSejour)
            ->setAsylumBackground($this->findInArray($sitAdm->droitsejour, SiSiaoItems::ASYLUM_BACKGROUND))
            ->setAgdrefId($sitAdm->numeroAgdref)
        ;

        $evaluationPerson->setEvalAdmPerson($evalAdmPerson);

        return $evalAdmPerson;
    }

    protected function createOrEditEvalProfPerson(EvaluationPerson $evaluationPerson, object $personne, object $diagSocial): ?EvalProfPerson
    {
        if ($personne->age < 16) {
            return null;
        }

        if (!$evalProfPerson = $evaluationPerson->getEvalProfPerson()) {
            $evalProfPerson = (new EvalProfPerson())
                ->setCommentEvalProf($diagSocial->commentaires)
                ->setEvaluationPerson($evaluationPerson);

            $this->em->persist($evalProfPerson);
        }

        $evalProfPerson
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
        ;

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

    protected function createOrEditEvalBudgetPerson(EvaluationPerson $evaluationPerson, object $personne, object $diagSocial): ?EvalBudgetPerson
    {
        if ($personne->age < 16) {
            return null;
        }

        if (!$evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson()) {
            $evalBudgetPerson = (new EvalBudgetPerson())
                ->setCommentEvalBudget($diagSocial->commentaireRessource."\n"
                    .$diagSocial->commentaireCharge."\n"
                    .$diagSocial->commentairesSituationBudgetaire."\n")
                ->setEvaluationPerson($evaluationPerson);

            $this->em->persist($evalBudgetPerson);
        }

        $evalBudgetPerson
            ->setOverIndebtRecord($this->findInArray($diagSocial->dossierSurendettement, SiSiaoItems::YES_NO))
            ->setOverIndebtRecordDate($this->convertDate($diagSocial->dateDepotDossierSurendettement))
            ->setSettlementPlan($this->findInArray($diagSocial->apurementDette, SiSiaoItems::YES_NO_BOOL))
            ->setMoratorium($this->findInArray($diagSocial->moratoire, SiSiaoItems::YES_NO_BOOL))
            ->setChargeComment($diagSocial->commentaireCharge)
            // ->setMonthlyRepaymentAmt($diagSocial->remboursementDettes)
            ->setDebtComment($diagSocial->commentairesSituationBudgetaire)
        ;

        $this->createResources($evalBudgetPerson, $diagSocial);
        $this->createCharges($evalBudgetPerson, $diagSocial);
        $this->createDebts($evalBudgetPerson, $diagSocial);

        $evaluationPerson->setEvalBudgetPerson($evalBudgetPerson);

        return $evalBudgetPerson;
    }

    protected function createResources(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $ressources = $this->get("ressourcePersonnes/diagnosticSocial/{$diagSocial->id}");
        $sumAmt = 0;

        foreach ($ressources as $ressource) {
            if (!$newEvalBudgetResource = $this->financeExists($evalBudgetPerson->getEvalBudgetResources(), $ressource->typeRessource->id)) {
                $newEvalBudgetResource = (new EvalBudgetResource());
            }

            $newEvalBudgetResource
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($this->findInArray($ressource->typeRessource, SiSiaoItems::RESOURCES))
                ->setAmount($ressource->montant)
                // ->setEnDate(null)
                ->setComment($ressource->commentaire);

            $this->em->persist($newEvalBudgetResource);

            $sumAmt += $ressource->montant;
        }

        $evalBudgetPerson
            ->setResource(count($ressources) > 0 ?
                Choices::YES : (true === $diagSocial->sansRessource ? Choices::NO : null))
            ->setResourcesAmt($sumAmt);

        $this->resourcesGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    protected function createCharges(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $charges = $this->get("chargePersonnes/diagnosticSocial/{$diagSocial->id}");
        $sumAmt = 0;

        foreach ($charges as $charge) {
            if (!$newEvalBudgetCharge = $this->financeExists($evalBudgetPerson->getEvalBudgetCharges(), $charge->typeCharge->id)) {
                $newEvalBudgetCharge = (new EvalBudgetCharge());
            }

            $newEvalBudgetCharge
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($this->findInArray($charge->typeCharge, SiSiaoItems::CHARGES))
                ->setAmount($charge->montant)
                ->setComment($charge->commentaire);

            $this->em->persist($newEvalBudgetCharge);

            $sumAmt += $charge->montant;
        }

        if ($diagSocial->remboursementDettes > 0) {
            if (!$newEvalBudgetCharge = $this->financeExists($evalBudgetPerson->getEvalBudgetCharges(), EvalBudgetCharge::REPAYMENT_DEBT)) {
                $newEvalBudgetCharge = (new EvalBudgetCharge());
            }

            $newEvalBudgetCharge
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType(EvalBudgetCharge::REPAYMENT_DEBT)
                ->setAmount($diagSocial->remboursementDettes);

            $this->em->persist($newEvalBudgetCharge);
        }

        $evalBudgetPerson
            ->setCharge(count($charges) > 0 ?
                Choices::YES : (true === $diagSocial->sansCharge ? Choices::NO : null))
            ->setChargesAmt($sumAmt);

        $this->chargesGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    protected function createDebts(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $dettes = $this->get("dettePersonnes/diagnosticSocial/{$diagSocial->id}");
        $sumAmt = 0;

        foreach ($dettes as $dette) {
            if (!$newEvalBudgetDebt = $this->financeExists($evalBudgetPerson->getEvalBudgetDebts(), $dette->typeDette->id)) {
                $newEvalBudgetDebt = (new EvalBudgetDebt());
            }

            $newEvalBudgetDebt
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($this->findInArray($dette->typeDette, SiSiaoItems::DEBTS))
                ->setAmount($dette->montant)
                ->setComment($dette->commentaire);

            $this->em->persist($newEvalBudgetDebt);

            $sumAmt += $dette->montant;
        }

        $evalBudgetPerson->setDebt(count($dettes) > 0 ?
            Choices::YES : (true === $diagSocial->sansDette ? Choices::NO : null))
            ->setDebtsAmt($sumAmt);

        $this->debtsGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    /**
     * @param Collection<EvaResource>|Collection<EvalBudgeCharge>|Collection<Debts> $finances
     *
     * @return EvaResource|Charge|Debt
     */
    protected function financeExists(Collection $finances, int $type): ?AbstractFinance
    {
        foreach ($finances as $finance) {
            if ($finance->getType() === $type) {
                return $finance;
            }
        }

        return null;
    }

    protected function createEvalInitPerson(SupportPerson $supportPerson, EvaluationPerson $evaluationPerson): ?EvalInitPerson
    {
        $evalAdmPerson = $evaluationPerson->getEvalAdmPerson();
        $evalSocialPerson = $evaluationPerson->getEvalSocialPerson();
        $evalProfPerson = $evaluationPerson->getEvalProfPerson();
        $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();

        $evalInitPerson = (new EvalInitPerson())
            ->setPaper($evalAdmPerson->getPaper())
            ->setPaperType($evalAdmPerson->getPaperType())
            ->setRightSocialSecurity($evalSocialPerson->getRightSocialSecurity())
            ->setSocialSecurity($evalSocialPerson->getSocialSecurity())
            ->setFamilyBreakdown($evalSocialPerson->getFamilyBreakdown())
            ->setFriendshipBreakdown($evalSocialPerson->getFriendshipBreakdown())
            ->setSupportPerson($supportPerson)
        ;

        if ($evalProfPerson) {
            $evalInitPerson
                ->setProfStatus($evalProfPerson->getProfStatus())
                ->setContractType($evalProfPerson->getContractType())
            ;
        }

        if ($evalBudgetPerson) {
            $evalInitPerson
                ->setResource($evalBudgetPerson->getResource())
                ->setResourcesAmt($evalBudgetPerson->getResourcesAmt())
                ->setRessourceOtherPrecision($evalBudgetPerson->getRessourceOtherPrecision())
                ->setDebt($evalBudgetPerson->getDebt())
                ->setDebtsAmt($evalBudgetPerson->getDebtsAmt())
            ;

            $this->createEvalInitResources($evalBudgetPerson, $evalInitPerson);
        }

        $this->em->persist($evalInitPerson);

        $evaluationPerson->setEvalInitPerson($evalInitPerson);

        return $evalInitPerson;
    }

    /**
     * Dupplique les ressources de la situation budgétaire dans la situation initiale.
     */
    protected function createEvalInitResources(EvalBudgetPerson $evalBudgetPerson, EvalInitPerson $evalInitPerson): EvalInitPerson
    {
        foreach ($evalBudgetPerson->getEvalBudgetResources() as $evalBudgetResource) {
            $evalInitResource = (new EvalInitResource())
                ->setEvalInitPerson($evalInitPerson)
                ->setType($evalBudgetResource->getType())
                ->setAmount($evalBudgetResource->getAmount())
                ->setComment($evalBudgetResource->getComment());

            $this->em->persist($evalInitResource);
        }

        return $evalInitPerson;
    }
}
