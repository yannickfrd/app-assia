<?php

namespace App\Service\SiSiao;

use App\Entity\People\Person;
use App\Form\Model\SiSiao\SiSiaoLogin;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiSiaoRequest
{
    use SiSiaoClientTrait;

    public const API = '/api/';

    public const API_PATHS = [
        'fiches/ficheIdentite/{fichePersonneId}',
        'fiches/ficheSynthese/{fichePersonneId}',
        'diagnosticSocials/{diagSocialId}',
        'ressourcePersonnes/diagnosticSocial/{diagSocialId}',
        'chargePersonnes/diagnosticSocial/{diagSocialId}',
        'dettePersonnes/diagnosticSocial/{diagSocialId}',
        'demandeInsertion/{id}',
        'demandeInsertion/getLastDemandeEnCours?idFiche={ficheGroupeId}',
        'situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={diagSocialId}',
        'demandeInsertion/historiquePersonne/{id}',
        'informationsenfant/getByFicheId/{id}',
        'login/user',
    ];

    public const REFERENTIELS = [
        'referentiels/' => [
            'situationDemandes',
            'motifDemandes',
            'dureeErrances',
            'compositions',
            'situationPersonnes',
            'situationSortieDemande',
            'papierIdentites',
            'droitSejours',
            'typeHebergementEnfant',
            'regroupementFamilial',
            'droitOuvertSecuriteSociales',
            'animaux',
            'typeContrats',
            'typeRessources',
            'typeCharges',
            'typeDettes',
            'evolutionsBudgetaires',
            'dispositif',
            'mesureAccompagnement',
            'categoriePlaces',
            'typePlaces',
            'typesEtablissementUn',
        ],
    ];

    private $client;
    private $session;

    private $url;

    private $headers;

    public function __construct(
        HttpClientInterface $client,
        SessionInterface $session,
        string $url
    ) {
        $this->client = $client;
        $this->session = $session;

        $this->url = $url.self::API;

        $this->headers = $this->session->get('sisiao_headers') ?? $this->getHeaders();
    }

    /**
     * Search by ID group.
     */
    public function searchById(string $id): ?object
    {
        $result = $this->get("personnes/search/findByCriteria?idGroupe={$id}");

        if (0 === $result->total) {
            $result = $this->get("personnes/search/findByCriteria?idFichePersonne={$id}");
        }

        return $result;
    }

    /**
     * Find people by ID group.
     */
    public function findPeople(string $id): ?array
    {
        $people = [];
        $result = $this->searchById($id);

        foreach ($result->result as $personne) {
            $person = (new Person())
                ->setLastname($personne->nom)
                ->setFirstname($personne->prenom)
                ->setBirthdate($this->convertDate($personne->dateNaissance))
                ->setGender('FEMME' === $personne->sexe ? 1 : 2);

            $people[] = [
                'lastname' => $person->getLastname(),
                'firstname' => $person->getFirstname(),
                'birthdate' => $person->getBirthdate()->format('d/m/Y'),
                'age' => (string) $person->getAge(),
                'gender' => $person->getGender(),
                'deptCode' => $personne->codeDepartements,
                'idPerson' => $personne->id,
                'idFiche' => $personne->ficheId,
                'idGroup' => $id,
            ];
        }

        return $people;
    }

    /**
     * Find a group by ID group.
     */
    public function findGroupById(string $id)
    {
        $ficheGroupe = $this->get("fiches/ficheIdentite/{$id}");
        dump($ficheGroupe);
        $diagSocialId = $ficheGroupe ? $ficheGroupe->demandeurprincipal->diagnosticSocial->id : null;

        if (null === $diagSocialId) {
            exit;
        }

        foreach ($ficheGroupe->personnes as $personne) {
            $diagSocialId = $personne->diagnosticSocial->id;
            // dump($this->get("diagnosticSocials/{$diagSocialId}"));
            // dump($this->get("ressourcePersonnes/diagnosticSocial/{$diagSocialId}"));
            // dump($this->get("chargePersonnes/diagnosticSocial/{$diagSocialId}"));
            // dump($this->get("dettePersonnes/diagnosticSocial/{$diagSocialId}"));
        }

        dump($this->get("situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={$diagSocialId}"));
        dump($this->get("demandeInsertion/getLastDemandeEnCours?idFiche={$id}"));

        exit;
    }

    /**
     * Get informations of connected user.
     */
    public function getUser()
    {
        return $this->get('login/user');
    }

    /**
     * Get current role of connected user.
     */
    protected function getCurrentRoleUser()
    {
        $user = $this->get('login/user');

        return $user->currentRole;
    }

    /*
     * Update evaluation by ID group.
     */
    public function updateEvaluation(string $id)
    {
        $this->login();

        $this->headers[] = 'Content-Type: application/json';

        $data = $this->get("fiches/ficheIdentite/{$id}");
        // dump($data);

        // Diagnostic social
        // $diagSocialId = $data->demandeurprincipal->diagnosticSocial->id;
        // $this->set('diagnosticSocials/'.$diagSocialId, $this->getDiagnosticSocial($diagSocialId));
        // // dump($this->get("diagnosticSocials/{$diagSocialId}"));

        // Situation famille
        // $sitFamilleId = $data->situationfamille->id;
        // $this->set('situationFamilles/'.$sitFamilleId, $this->getSituationFamilles($sitFamilleId));
        // // dump($this->get("fiches/ficheIdentite/{$id}")->situationfamille);

        // Situation sociale
        // $sitSocialeId = $data->situationsociale->id;
        // $this->set('situationSociales/'.$sitSocialeId, $this->getDiagnosticSocial($sitSocialeId));
        // // dump($this->get("fiches/ficheIdentite/{$id}")->situationsociale);

        // Situation par rapport au logement
        // $sitLogement = $this->get("situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={$diagSocialId}");
        // $sitLogementId = $sitLogement->id;
        // $this->set('situationParRapportAuLogement/'.$sitLogementId, $this->getSituationParRapportAuLogement($sitLogementId, $diagSocialId));
        // // dump($this->get("situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={$diagSocialId}"));

        foreach ($data->personnes as $personne) {
            $diagSocialId = $personne->diagnosticSocial->id;
            // Situation administrative
            // $sitAdmId = $personne->situationadministrative->id;
            // $this->set('situationAdministratives/'.$sitAdmId, $this->getSituationAdministratives($sitAdmId));

            // Resources
            // $ressources = $this->get("ressourcePersonnes/diagnosticSocial/{$diagSocialId}");
            // foreach ($ressources as $ressource) {
            //     $ressourceId = $ressource->id;
            //     $this->set("ressourcePersonnes/{$ressourceId}", $this->getRessourcePersonne($ressourceId, $diagSocialId));
            // }

            // Charges
            // $charges = $this->get("chargePersonnes/diagnosticSocial/{$diagSocialId}");
            // foreach ($charges as $charge) {
                //     $chargeId = $charge->id;
                //     $this->set("chargePersonnes/{$chargeId}", $this->getChargePersonne($chargeId, $diagSocialId));
                // }

            // Dettes
            // $dettes = $this->get("dettePersonnes/diagnosticSocial/{$diagSocialId}");
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

    public function updateSiaoRequest(string $id)
    {
        $this->login();

        $this->headers[] = 'Content-Type: application/json';

        $now = (new \DateTime())->format('Y-m-d\T00:00');
        $demandeInsertion = $this->get("demandeInsertion/getLastDemandeEnCours?idFiche={$id}");
        $demandeInsertion->dateTransmissionSiao = $now;
        $demandeInsertion->dateModification = $now;

        $this->set('demandeInsertion/', $demandeInsertion);

        dd($this->get('demandeInsertion/'.$demandeInsertion->id));
    }

    public function login(?SiSiaoLogin $siSiaoUser = null): array
    {
        if ($this->isConnected()) {
            return [
                'isConnected' => true,
                'code' => null,
            ];
        }

        try {
            $response = $this->client->request('POST', $this->url.'login', [
                'headers' => $this->headers = $this->getHeaders(),
                'body' => [
                    'username' => $siSiaoUser ? $siSiaoUser->getUsername() : '',
                    'password' => $siSiaoUser ? $siSiaoUser->getPassword() : '',
                ],
            ]);

            array_push($this->headers, 'Cookie: '.join('; ', $response->getHeaders()['set-cookie']));

            $this->session->set('sisiao_headers', $this->headers);

            return [
                'isConnected' => Response::HTTP_UNAUTHORIZED !== $response->getStatusCode(),
                'code' => $response->getStatusCode(),
            ];
        } catch (\Exception $e) {
            return [
                'isConnected' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    public function isConnected(): bool
    {
        $response = $this->client->request('GET', $this->url, ['headers' => $this->headers]);

        return Response::HTTP_UNAUTHORIZED !== $response->getStatusCode();
    }

    public function logout(): void
    {
        $this->client->request('POST', $this->url.'logout', ['headers' => $this->headers]);
    }

    /**
     * @return array|object
     */
    public function get(string $path)
    {
        $this->login();
        $response = $this->client->request('GET', $this->url.$path, ['headers' => $this->headers]);
        $code = $response->getStatusCode();

        if (Response::HTTP_FORBIDDEN === $code) {
            return null;
        }

        if (Response::HTTP_OK !== $code) {
            throw new \Exception('Error response with status code '.$code.' for the path '.$path, $code);
        }

        $content = $response->getContent();
        $headers = $response->getHeaders();

        if (isset($headers['content-encoding']) && in_array('gzip', $headers['content-encoding'])) {
            $content = gzdecode($content);
        }

        return json_decode($content);
    }

    public function set(string $path, object $data)
    {
        $response = $this->client->request('PUT', $this->url.$path, [
            'headers' => $this->headers,
            'json' => $data,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception('Error response with status code '.$response->getStatusCode().' for the path '.$this->url.$path);
        }
    }

    public function getReferentielPaths(): array
    {
        $paths = [];

        foreach (self::REFERENTIELS as $key => $values) {
            foreach ($values as $value) {
                $paths[] = $key.$value;
            }
        }

        return $paths;
    }

    public function getReferentiels(): void
    {
        $this->login();

        foreach ($this->getReferentielPaths() as $path) {
            dump($this->get($path));
        }
        exit;
    }

    public function getReferentielsToString(): string
    {
        $this->login();

        $referentielsString = '';

        foreach (self::REFERENTIELS['referentiels/'] as $key => $value) {
            $referentiel = $this->get('referentiels/'.$value);
            $referentielsString .= '// '.strtoupper($value).
                '<br/>public const '.strtoupper($value).' = [<br/>';

            foreach ($referentiel as $item) {
                if (isset($item->libelle)) {
                    $referentielsString .= "{$item->id} => null, // {$item->libelle}<br/>";
                    continue;
                }
                if (isset($item->nom)) {
                    $referentielsString .= "{$item->id} => null, // {$item->nom}".
                    (isset($item->dispositif) ? ' - '.$item->dispositif->libelle : null).'<br/>';
                }
            }

            $referentielsString .= '];<br/><br/>';

            $paths[] = $key.$value;
        }

        return $referentielsString;
    }

    protected function getHeaders(): array
    {
        return [
            'Accept: application/json, text/plain, */*',
            'Accept-Encoding: gzip, deflate, br',
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'Connection: keep-alive',
            // 'Sec-Fetch-Dest: empty',
            // 'Sec-Fetch-Mode: cors',
            // 'Sec-Fetch-Site: same-origin',
        ];
    }

    protected function getErrorMessage(\Exception $e)
    {
        $code = $e->getCode();

        switch ($code) {
            case Response::HTTP_FORBIDDEN:
                $message = "Vous n'êtes n'avez pas les droits nécessaires";
                break;
            case Response::HTTP_UNAUTHORIZED:
                $message = "Vous n'êtes plus connecté au SI-SIAO";
                break;
            default:
                $message = "Une erreur s'est produite";
                break;
        }

        return $message." (erreur $code).";
    }
}
