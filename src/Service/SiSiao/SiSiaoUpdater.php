<?php

namespace App\Service\SiSiao;

use App\Notification\ExceptionNotification;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class to import evaluation from API SI-SIAO.
 */
class SiSiaoUpdater extends SiSiaoClient
{
    protected $flashBag;
    protected $exceptionNotification;

    /** @var int ID fiche groupe SI-SIAO */
    protected $id;
    /** @var object */
    protected $ficheGroupe;

    public function __construct(
        HttpClientInterface $client,
        RequestStack $requestStack,
        FlashBagInterface $flashBag,
        ExceptionNotification $exceptionNotification,
        string $url
    ) {
        parent::__construct($client, $requestStack, $url);

        $this->flashBag = $flashBag;
        $this->exceptionNotification = $exceptionNotification;
    }

    /*
     * Update evaluation by ID group.
     */
    public function updateEvaluation(int $id)
    {
        $this->login();

        $this->headers[] = 'Content-Type: application/json';

        /** @var object $data */
        $data = $this->get("/fiches/ficheIdentite/{$id}");
        // dump($data);

        // Diagnostic social
        // $diagSocialId = $data->demandeurprincipal->diagnosticSocial->id;
        // $this->set('diagnosticSocials/'.$diagSocialId, $this->getDiagnosticSocial($diagSocialId));
        // // dump($this->get("/diagnosticSocials/{$diagSocialId}"));

        // Situation famille
        // $sitFamilleId = $data->situationfamille->id;
        // $this->set('situationFamilles/'.$sitFamilleId, $this->getSituationFamilles($sitFamilleId));
        // // dump($this->get("/fiches/ficheIdentite/{$id}")->situationfamille);

        // Situation sociale
        // $sitSocialeId = $data->situationsociale->id;
        // $this->set('situationSociales/'.$sitSocialeId, $this->getDiagnosticSocial($sitSocialeId));
        // // dump($this->get("/fiches/ficheIdentite/{$id}")->situationsociale);

        // Situation par rapport au logement
        // $sitLogement = $this->get("/situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={$diagSocialId}");
        // $sitLogementId = $sitLogement->id;
        // $this->set('situationParRapportAuLogement/'.$sitLogementId, $this->getSituationParRapportAuLogement($sitLogementId, $diagSocialId));
        // // dump($this->get("/situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={$diagSocialId}"));

        foreach ($data->personnes as $personne) {
            $diagSocialId = $personne->diagnosticSocial->id;
            // Situation administrative
            // $sitAdmId = $personne->situationadministrative->id;
            // $this->set('situationAdministratives/'.$sitAdmId, $this->getSituationAdministratives($sitAdmId));

            // Resources
            // $ressources = $this->get("/ressourcePersonnes/diagnosticSocial/{$diagSocialId}");
            // foreach ($ressources as $ressource) {
            //     $ressourceId = $ressource->id;
            //     $this->set("ressourcePersonnes/{$ressourceId}", $this->getRessourcePersonne($ressourceId, $diagSocialId));
            // }

            // Charges
            // $charges = $this->get("/chargePersonnes/diagnosticSocial/{$diagSocialId}");
            // foreach ($charges as $charge) {
                //     $chargeId = $charge->id;
                //     $this->set("chargePersonnes/{$chargeId}", $this->getChargePersonne($chargeId, $diagSocialId));
                // }

            // Dettes
            // $dettes = $this->get("/dettePersonnes/diagnosticSocial/{$diagSocialId}");
            // foreach ($dettes as $dette) {
            //     $detteId = $dette->id;
            //     $this->set("dettePersonnes/{$detteId}", $this->getDettePersonne($detteId, $diagSocialId));
            // }
        }

        exit;
    }

    /*
     * Update a SIAO request by ID group.
     */

    public function updateSiaoRequest(int $id)
    {
        $this->login();

        $this->headers[] = 'Content-Type: application/json';

        $now = (new \DateTime())->format('Y-m-d\T00:00');

        /** @var object $demandeInsertion */
        $demandeInsertion = $this->get("/demandeInsertion/getLastDemandeEnCours?idFiche={$id}");
        $demandeInsertion->dateTransmissionSiao = $now;
        $demandeInsertion->dateModification = $now;

        $this->set('demandeInsertion/', $demandeInsertion);

        dd($this->get('demandeInsertion/'.$demandeInsertion->id));
    }
}
