<?php

namespace App\Service\SiSiao;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetCharge;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalBudgetResource;
use App\Entity\Evaluation\EvalFamilyGroup;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\EvalInitResource;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialGroup;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\People\Person;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;
use App\Notification\ExceptionNotification;
use App\Service\Evaluation\EvaluationCompletionChecker;
use App\Service\Evaluation\EvaluationDuplicator;
use App\Service\Note\NoteManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class to import evaluation from API SI-SIAO.
 */
class SiSiaoEvaluationImporter extends SiSiaoClient
{
    protected $em;
    protected $evaluationDuplicator;
    protected $evaluationCompletionChecker;
    protected $user;
    protected $requestStack;
    protected $flashBag;
    protected $translator;
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
        EvaluationDuplicator $evaluationDuplicator,
        EvaluationCompletionChecker $evaluationCompletionChecker,
        Security $security,
        ExceptionNotification $exceptionNotification,
        TranslatorInterface $translator,
        string $url
    ) {
        parent::__construct($client, $requestStack, $url, $translator);

        $this->em = $em;
        $this->evaluationDuplicator = $evaluationDuplicator;
        $this->evaluationCompletionChecker = $evaluationCompletionChecker;
        $this->user = $security->getUser();
        $this->exceptionNotification = $exceptionNotification;
        $this->requestStack = $requestStack;

        /** @var Session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
        $this->translator = $translator;
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

            $this->flashBag->add('danger', $this->translator->trans('sisiao.evaluation.import_failed', [
                'error_message' => $this->getErrorMessage($e),
            ], 'app'));

            return null;
        }
    }

    protected function tryImportEvaluation(SupportGroup $supportGroup): ?EvaluationGroup
    {
        if (false === $this->isConnected()) {
            $this->flashBag->add('danger', 'sisiao.evaluation.import_failed_no_connected');

            return null;
        }

        $this->id = (int) $supportGroup->getPeopleGroup()->getSiSiaoId();

        if (!$this->id) {
            $this->flashBag->add('warning', 'sisiao.no_group_id');

            return null;
        }

        /** @var object $result */
        $result = $this->searchById($this->id);

        if (0 === $result->total) {
            $this->flashBag->add('warning', $this->translator->trans('sisiao.group_id_no_exist', [
                'sisiao_id' => $this->id,
            ], 'app'));

            return null;
        }

        $this->evaluationGroup = $supportGroup->getEvaluationsGroup()->first();

        $this->ficheGroupe = $this->get("/fiches/ficheIdentite/{$this->id}");

        $evaluationGroup = $this->createOrEditEvaluationGroup($supportGroup);

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $personne = $this->matchPerson($supportPerson->getPerson());
            if ($personne) {
                $this->createOrEditEvaluationPerson($evaluationGroup, $supportPerson, $personne);
            }
        }

        $this->createOrEditEvalBudgetGroup($evaluationGroup);

        if (!$supportGroup->getEvalInitGroup()) {
            $this->evaluationDuplicator->createEvalInitGroup($supportGroup, $evaluationGroup);
        }

        $supportGroup->setEvaluationScore($this->evaluationCompletionChecker->getScore($evaluationGroup)['score']);

        $this->importNotes($supportGroup);

        $this->em->flush();

        $this->flashBag->add('success', $this->evaluationGroup
            ? 'sisiao.evaluation.updated_successfully'
            : 'sisiao.evaluation.imported_successfully'
        );

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
        $demandeSiao = $this->get("/demandeInsertion/getLastDemandeEnCours?idFiche={$this->id}");

        $this->createOrEditEvalSocialGroup($evaluationGroup, $sitSociale, $demandeSiao);
        $this->createOrEditEvalFamilyGroup($evaluationGroup);
        $this->createOrEditEvalHousingGroup($evaluationGroup, $demandeSiao);
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
            ->setReasonRequest($this->findInArray($demandeSiao && is_object($demandeSiao) ?
                $demandeSiao->motifDemande : null, SiSiaoItems::REASON_REQUEST))
            ->setWanderingTime($this->getWanderingTime())
            ->setAnimal(count($animaux) > 0 ? Choices::YES : Choices::NO)
            ->setAnimalType($this->getAnimalType())
        ;

        $evaluationGroup->setEvalSocialGroup($evalSocialGroup);

        return $evalSocialGroup;
    }

    protected function getWanderingTime(): ?int
    {
        if (null === $this->ficheGroupe->contactPrincipal) {
            return null;
        }

        $wanderingDate = $this->convertDate($this->ficheGroupe->contactPrincipal->dateDebutErrance);

        if (!$wanderingDate) {
            return null;
        }

        $delay = $wanderingDate->diff(new \DateTime());

        // Plus de 10 ans
        if ($delay->y >= 10) {
            return 8;
        }
        // 5 ans - 10 ans
        if ($delay->y >= 5) {
            return 7;
        }
        // 2 ans - 5 ans
        if ($delay->y >= 2) {
            return 6;
        }
        // 1 an - 2 ans
        if ($delay->y >= 1) {
            return 5;
        }
        // 6 mois - 1 an
        if ($delay->m >= 6) {
            return 4;
        }
        // 1 mois - 6 mois
        if ($delay->m >= 1) {
            return 3;
        }
        // 1 semaine - 1 mois
        if ($delay->d >= 7) {
            return 2;
        }
        // Moins d'une semaine
        return 1;
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
    protected function createOrEditEvalHousingGroup(EvaluationGroup $evaluationGroup, $demandeSiao = null): EvalHousingGroup
    {
        /** @var object */
        $sitLogement = $this->get("/situationParRapportAuLogement/getByFicheId?ficheId={$this->id}");
        if (!$evalHousingGroup = $evaluationGroup->getEvalHousingGroup()) {
            $evalHousingGroup = (new EvalHousingGroup())
                ->setCommentEvalHousing($sitLogement ? $sitLogement->commentaireSituationLogement : null)
                ->setEvaluationGroup($evaluationGroup)
            ;

            $this->em->persist($evalHousingGroup);
        }

        $evalHousingGroup->setExpulsionInProgress($this->findInArray($sitLogement->expulsion, SiSiaoItems::YES_NO));

        if ($sitLogement) {
            $evalHousingGroup
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
                ->setSiaoRequest(Choices::YES)
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

    protected function getSocialHousingRequestId(?array $numerosUniqueLogementSocial): ?string
    {
        if (null === $numerosUniqueLogementSocial) {
            return null;
        }

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
            $diagSocial = $this->get("/diagnosticSocials/{$diagSocialId}");
            $this->createOrEditEvalProfPerson($evaluationPerson, $personne, $diagSocial);
            $this->createOrEditEvalBudgetPerson($evaluationPerson, $personne, $diagSocial);
        }

        if (!$supportPerson->getEvalInitPerson()) {
            $this->evaluationDuplicator->createEvalInitPerson($supportPerson, $evaluationPerson);
        }

        return $evaluationPerson;
    }

    protected function createOrEditEvalSocialPerson(EvaluationPerson $evaluationPerson, object $personne): ?EvalSocialPerson
    {
        /** @var object */
        $sitAdm = $personne->situationadministrative;

        if (!$evalSocialPerson = $evaluationPerson->getEvalSocialPerson()) {
            $evalSocialPerson = (new EvalSocialPerson())
                ->setCommentEvalSocialPerson($personne->commentairePMR)
                ->setEvaluationPerson($evaluationPerson)
            ;

            $this->em->persist($evalSocialPerson);
        }

        $evalSocialPerson
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
            ->setHealthProblem(in_array(SiSiaoItems::YES, [$personne->problemeMobilite, $personne->fauteuilRoulant]) ?
                EvaluationChoices::YES : EvaluationChoices::NO)
            ->setReducedMobility($this->findInArray($personne->problemeMobilite, SiSiaoItems::YES_NO_STRING_TO_BOOL))
            ->setWheelchair($this->findInArray($personne->fauteuilRoulant, SiSiaoItems::YES_NO_STRING_TO_BOOL))
            ->setViolenceVictim(true === $personne->victimeviolence ? Choices::YES : Choices::NO)
            ->setDomViolenceVictim($personne->typevictime && strstr($personne->typevictime, 'VIOLENCES_CONJUGALES') ? Choices::YES : Choices::NO)
        ;

        if ($evaluationPerson->getSupportPerson()->getHead()) {
            /** @var object */
            $sitSocial = $this->ficheGroupe->situationsociale;

            $evalSocialPerson
                ->setMedicalFollowUp($this->findInArray($sitSocial->suivimedical, SiSiaoItems::YES_NO))
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
            ->setAsylumBackground($this->findInArray($sitAdm->statutAdministratifParticulier, SiSiaoItems::ASYLUM))
            ->setAsylumStatus($this->findInArray($sitAdm->statutAdministratifParticulier, SiSiaoItems::ASYLUM_STATUS))
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
            ->setJobCenterId(null === $evalProfPerson->getJobCenterId() ?
                $this->findInArray($diagSocial->statutDemandeurEmploi, SiSiaoItems::JOB_CENTER) : null)
            ->setProfStatus($this->getProfStatus($diagSocial))
            ->setContractType($this->getContractType($diagSocial))
            ->setWorkingTime($this->findInArray($diagSocial->tempsTravail, SiSiaoItems::WORKING_TIME))
            ->setContractStartDate($this->convertDate($diagSocial->dateDebutContrat))
            ->setContractEndDate($this->convertDate($diagSocial->dateFinContrat))
            ->setWorkingHours(true === $diagSocial->horairesDecales ? 'Horaires décalés' : null)
            ->setWorkPlace($diagSocial->communeEmploi.
                ($diagSocial->departementEmploi ? ' ('.$diagSocial->departementEmploi.')' : null))
            ->setRqth($this->findInArray($diagSocial->rqth, SiSiaoItems::YES_NO))
            ->setTransportMeansType($this->getTransportMeansType($diagSocial))
            ->setTransportMeans($this->getTransportMeans($diagSocial))
        ;

        $evaluationPerson->setEvalProfPerson($evalProfPerson);

        return $evalProfPerson;
    }

    protected function getProfStatus(object $diagSocial): ?int
    {
        if (SiSiaoItems::YES === $diagSocial->enEmploi) {
            return EvalProfPerson::PROF_STATUS_EMPLOYEE;
        }
        if (SiSiaoItems::YES === $diagSocial->enFormation) {
            if ('FORMATION_INITIALE' === $diagSocial->typeFormation) {
                return EvalProfPerson::PROF_STATUS_STUDENT;
            }

            return EvalProfPerson::PROF_STATUS_IN_TRAINING;
        }
        if (SiSiaoItems::YES === $diagSocial->demandeur) {
            return EvalProfPerson::PROF_STATUS_JOB_SEEKER;
        }
        if (SiSiaoItems::YES === $diagSocial->retraite) {
            return EvalProfPerson::PROF_STATUS_RETIRED;
        }

        return null;
    }

    protected function getContractType(object $diagSocial): ?int
    {
        $contractType = $this->findInArray($diagSocial->typeContrat, SiSiaoItems::CONTRACT_TYPE);

        if ($contractType) {
            return $contractType;
        }

        if ('FORMATION_INITIALE' === $diagSocial->typeFormation
            && 'ALTERNANCE' === $diagSocial->soustypeFormation) {
            return 4;
        }

        return null;
    }

    protected function getTransportMeansType(object $diagSocial): ?int
    {
        foreach (SiSiaoItems::TRANSFORT_MEANS as $key => $value) {
            foreach ($diagSocial->typesMoyenLocomotion as $moyenLocomotion) {
                if (isset($moyenLocomotion->id) && $moyenLocomotion->id == $key) {
                    return $value;
                }
            }
        }

        return null;
    }

    protected function getTransportMeans(object $diagSocial): string
    {
        $transports = [];

        foreach ($diagSocial->typesMoyenLocomotion as $moyenLocomotion) {
            $transports[] = $moyenLocomotion->libelle;
        }

        return join(', ', $transports);
    }

    protected function createOrEditEvalBudgetPerson(EvaluationPerson $evaluationPerson, object $personne, object $diagSocial): ?EvalBudgetPerson
    {
        if ($personne->age < 16) {
            return null;
        }

        if (!$evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson()) {
            $evalBudgetPerson = (new EvalBudgetPerson())
                ->setCommentEvalBudget(
                    ($diagSocial->commentaireRessource ? $diagSocial->commentaireRessource."\n" : '').
                    ($diagSocial->commentaireCharge ? $diagSocial->commentaireCharge."\n" : '').
                    ($diagSocial->commentairesSituationBudgetaire ? $diagSocial->commentairesSituationBudgetaire."\n" : ''))
                ->setEvaluationPerson($evaluationPerson)
            ;

            $this->em->persist($evalBudgetPerson);
        }

        $evalBudgetPerson
            ->setResource($this->findInArray($diagSocial->avecRessource, SiSiaoItems::YES_NO))
            ->setCharge($this->findInArray($diagSocial->avecCharge, SiSiaoItems::YES_NO))
            ->setChargeComment($diagSocial->commentaireCharge)
            ->setDebtComment($diagSocial->commentairesSituationBudgetaire)
            ->setDebt($this->findInArray($diagSocial->avecDette, SiSiaoItems::YES_NO))
            ->setDebtsAmt($diagSocial->montantDette)
            ->setOverIndebtRecord($this->findInArray($diagSocial->dossierSurendettement, SiSiaoItems::YES_NO))
            ->setOverIndebtRecordDate($this->convertDate($diagSocial->dateDepotDossierSurendettement))
            ->setSettlementPlan(true === $diagSocial->apurementDette ? 4 : null)
            ->setMoratorium(true === $diagSocial->moratoire ? Choices::YES : Choices::NO)
        ;

        $this->createResources($evalBudgetPerson, $diagSocial);
        $this->createCharges($evalBudgetPerson, $diagSocial);

        $evaluationPerson->setEvalBudgetPerson($evalBudgetPerson);

        return $evalBudgetPerson;
    }

    protected function createResources(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $ressources = $this->get("/ressourcePersonnes/diagnosticSocial/{$diagSocial->id}");
        $sumAmt = 0;

        foreach ($ressources as $ressource) {
            $resourcetype = $this->findInArray($ressource->typeRessource, SiSiaoItems::RESOURCES);
            $evalBudgetResource = $this->financeExists($evalBudgetPerson->getEvalBudgetResources(), $resourcetype);

            if (null === $evalBudgetResource) {
                $evalBudgetResource = (new EvalBudgetResource());
                $evalBudgetPerson->addEvalBudgetResource($evalBudgetResource);
            }

            $evalBudgetResource
                ->setEndDate($this->convertDate($ressource->dateFinPrevisionnelle))
                ->setType($resourcetype)
                ->setAmount($ressource->montant)
                ->setComment($ressource->commentaire ? htmlspecialchars(substr($ressource->commentaire, 0, 100)) : null)
            ;

            $evalBudgetPerson->addEvalBudgetResource($evalBudgetResource);

            $this->em->persist($evalBudgetResource);

            $sumAmt += $ressource->montant;
        }

        $evalBudgetPerson->setResourcesAmt($sumAmt);

        $this->resourcesGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    protected function createCharges(EvalBudgetPerson $evalBudgetPerson, object $diagSocial): EvalBudgetPerson
    {
        $charges = $this->get("/chargePersonnes/diagnosticSocial/{$diagSocial->id}");
        $sumAmt = 0;

        foreach ($charges as $charge) {
            $chargeType = $this->findInArray($charge->typeCharge, SiSiaoItems::CHARGES);
            $evalBudgetCharge = $this->financeExists($evalBudgetPerson->getEvalBudgetCharges(), $chargeType);

            if (null === $evalBudgetCharge) {
                $evalBudgetCharge = (new EvalBudgetCharge());
                $evalBudgetPerson->addEvalBudgetCharge($evalBudgetCharge);
            }

            $evalBudgetCharge
                ->setType($chargeType)
                ->setAmount($charge->montant)
                ->setComment($charge->commentaire ? htmlspecialchars(substr($charge->commentaire, 0, 100)) : null)
            ;

            $evalBudgetPerson->addEvalBudgetCharge($evalBudgetCharge);

            $this->em->persist($evalBudgetCharge);

            $sumAmt += $charge->montant;
        }

        if ($diagSocial->remboursementDettes > 0) {
            if (!$evalBudgetCharge = $this->financeExists($evalBudgetPerson->getEvalBudgetCharges(), EvalBudgetCharge::REPAYMENT_DEBT)) {
                $evalBudgetCharge = (new EvalBudgetCharge());
            }

            $evalBudgetCharge
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType(EvalBudgetCharge::REPAYMENT_DEBT)
                ->setAmount($diagSocial->remboursementDettes)
            ;

            $this->em->persist($evalBudgetCharge);
        }

        $evalBudgetPerson->setChargesAmt($sumAmt);

        $this->chargesGroupAmt += $sumAmt;

        return $evalBudgetPerson;
    }

    /**
     * @param Collection<EvalBudgetResource>|Collection<EvalBudgetCharge> $finances
     *
     * @return EvalBudgetResource|EvalBudgetCharge|null
     */
    protected function financeExists(Collection $finances, int $type)
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
                ->setDebt($evalBudgetPerson->getDebt())
                ->setDebtsAmt($evalBudgetPerson->getDebtsAmt())
            ;

            $this->createEvalInitResources($evalBudgetPerson, $evalInitPerson);
        }

        $this->em->persist($evalInitPerson);

        $supportPerson->setEvalInitPerson($evalInitPerson);

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
                ->setComment($evalBudgetResource->getComment())
            ;

            $this->em->persist($evalInitResource);
        }

        return $evalInitPerson;
    }

    protected function importNotes(SupportGroup $supportGroup): void
    {
        try {
            $results = $this->get('/notes/findRecentNotesByFicheId?ficheId='.$this->id);

            if (0 === count($results)) {
                return;
            }

            $noteRepo = $this->em->getRepository(Note::class);
            /** @var Collection<Note> */
            $notes = new ArrayCollection();

            foreach ($results as $result) {
                if (!$note = $noteRepo->findOneBy([
                    'comment' => $result->id,
                    'supportGroup' => $supportGroup->getId(),
                ])) {
                    $note = (new Note())
                        ->setTitle('Note import SI-SIAO')
                        ->setType(Note::TYPE_NOTE)
                        ->setStatus(null)
                        ->setComment((string) $result->id)
                    ;

                    $this->em->persist($note);

                    $supportGroup->addNote($note);
                    $notes->add($note);
                }

                $note->setContent($result->contenu);
            }

            if ($notes->count() > 0) {
                NoteManager::deleteCacheItems($notes->first());
            }
        } catch (\Exception $e) {
            $this->flashBag->add('warning', $this->translator->trans('sisiao.note.import_failed', [
                'error_message' => $this->getErrorMessage($e),
            ], 'app'));
        }
    }
}
