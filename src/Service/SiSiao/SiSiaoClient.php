<?php

namespace App\Service\SiSiao;

use App\Entity\People\Person;
use App\Form\Model\SiSiao\SiSiaoLogin;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiSiaoClient
{
    public const API = '/api';

    public const API_PATHS = [
        '/fiches/ficheIdentite/{fichePersonneId}',
        '/fiches/ficheSynthese/{fichePersonneId}',
        '/diagnosticSocials/{diagSocialId}',
        '/ressourcePersonnes/diagnosticSocial/{diagSocialId}',
        '/chargePersonnes/diagnosticSocial/{diagSocialId}',
        '/dettePersonnes/diagnosticSocial/{diagSocialId}',
        '/demandeInsertion/{id}',
        '/demandeInsertion/getLastDemandeEnCours?idFiche={ficheGroupeId}',
        '/situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={diagSocialId}',
        '/demandeInsertion/historiquePersonne/{id}',
        '/informationsenfant/getByFicheId/{id}',
        '/login/user',
    ];

    private $client;
    private $session;
    private $url;
    private $headers;

    public function __construct(HttpClientInterface $client, RequestStack $requestStack, string $url)
    {
        $this->client = $client;
        $this->session = $requestStack->getSession();
        $this->url = $url.self::API;
        $this->headers = $this->session->get('sisiao.headers') ?? $this->getHeaders();
    }

    /**
     * Search by ID group.
     *
     * @return array|object
     */
    public function searchById(int $id)
    {
        $result = $this->get("/personnes/search/findByCriteria?idGroupe={$id}");

        if (!is_object($result) || 0 === $result->total) {
            $result = $this->get("/personnes/search/findByCriteria?idFichePersonne={$id}");
        }

        return $result;
    }

    /**
     * Find people by ID group.
     */
    public function findPeople(int $id): ?array
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
     * Get group by id from API SI-SIAO.
     */
    public function findGroupById(int $id): array
    {
        $result = $this->searchById($id);

        if (!is_object($result)) {
            return $result;
        }

        if (0 === $result->total) {
            return [
                'alert' => 'warning',
                'group' => null,
                'msg' => 'Aucun résultat.',
            ];
        }

        $data = $this->get("/fiches/ficheIdentite/{$id}");

        $dp = $data->demandeurprincipal;

        if ('Personne' === $data->typefiche) {
            foreach ($dp->fiches as $fiche) {
                if ('Groupe' === $fiche->typefiche) {
                    $personnes = $fiche->personnes;
                    $idGroupe = $fiche->id;
                }
            }
            if (!isset($personnes)) {
                $fiche = $dp->fiches[count($dp->fiches) - 1];
                $personnes = $fiche->personnes;
                $idGroupe = $fiche->id;
            }
        } else {
            $personnes = $data->personnes;
            $idGroupe = $data->id;
        }

        return [
            'alert' => 'success',
            'group' => [
                'composition' => $data->composition,
                'dp' => $dp,
                'personnes' => $personnes,
                'idGroupe' => $idGroupe,
            ],
        ];
    }

    /**
     * Dump a group by ID group.
     */
    public function dumpGroupById(int $id)
    {
        $ficheGroupe = $this->get("/fiches/ficheIdentite/{$id}");
        dump($ficheGroupe);
        $diagSocialId = $ficheGroupe ? $ficheGroupe->demandeurprincipal->diagnosticSocial->id : null;

        if (null === $diagSocialId) {
            exit;
        }

        foreach ($ficheGroupe->personnes as $personne) {
            $diagSocialId = $personne->diagnosticSocial->id;
            // dump($this->get("/diagnosticSocials/{$diagSocialId}"));
            // dump($this->get("/ressourcePersonnes/diagnosticSocial/{$diagSocialId}"));
            // dump($this->get("/chargePersonnes/diagnosticSocial/{$diagSocialId}"));
            // dump($this->get("/dettePersonnes/diagnosticSocial/{$diagSocialId}"));
        }

        dump($this->get("/situationParRapportAuLogement/getByDiagnosticSocialId?diagnosticSocialId={$diagSocialId}"));
        dump($this->get("/demandeInsertion/getLastDemandeEnCours?idFiche={$id}"));

        exit;
    }

    /**
     * Get informations of connected user.
     *
     * @return array|object
     */
    public function getUser()
    {
        return $this->get('/login/user');
    }

    /**
     * Get current role of connected user.
     */
    protected function getCurrentRoleUser(): JsonResponse
    {
        $user = $this->get('/login/user');

        return $user->currentRole;
    }

    public function login(?SiSiaoLogin $siSiaoLogin = null): array
    {
        if ($this->isConnected()) {
            return [
                'isConnected' => true,
                'code' => null,
            ];
        }

        try {
            $response = $this->client->request('POST', $this->url.'/login', [
                'headers' => $this->headers = $this->getHeaders(),
                'body' => [
                    'username' => $siSiaoLogin ? $siSiaoLogin->getUsername() : '',
                    'password' => $siSiaoLogin ? $siSiaoLogin->getPassword() : '',
                ],
            ]);

            array_push($this->headers, 'Cookie: '.join('; ', $response->getHeaders()['set-cookie']));

            $this->session->set('sisiao.headers', $this->headers);

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
        $this->client->request('POST', $this->url.'/logout', ['headers' => $this->headers]);
    }

    /**
     * @return array|object|null
     */
    public function get(string $path)
    {
        $this->login();
        $response = $this->client->request('GET', $this->url.$path, ['headers' => $this->headers]);
        $code = $response->getStatusCode();

        if (Response::HTTP_OK !== $code) {
            return [
                'alert' => 'danger',
                'msg' => 'Error response with status code '.$code.' for the path '.$path, $code,
                'code' => $code,
            ];
        }

        $content = $response->getContent();
        $headers = $response->getHeaders();

        if (isset($headers['content-encoding']) && in_array('gzip', $headers['content-encoding'])) {
            $content = gzdecode($content);
        }

        return json_decode($content);
    }

    public function set(string $path, object $data): void
    {
        $response = $this->client->request('PUT', $this->url.$path, [
            'headers' => $this->headers,
            'json' => $data,
        ]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new \Exception('Error response with status code '.$response->getStatusCode().' for the path '.$this->url.$path);
        }
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

    protected function getErrorMessage(\Exception $e): string
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

        return $message.(0 !== $code ? " (erreur $code)" : '').'. ';
    }

    /**
     * Convert string date to Datetime object.
     */
    public static function convertDate(?string $date): ?\DateTime
    {
        if (null === $date) {
            return null;
        }

        if (str_contains($date, '-')) {
            return new \DateTime($date);
        }

        if (str_contains($date, '/')) {
            $dateArray = explode('/', $date);
            if (3 === count($dateArray)) {
                return new \DateTime($dateArray[2].'-'.$dateArray[1].'-'.$dateArray[0]);
            }
        }

        return null;
    }

    /**
     * @param int|object|null $needle
     *
     * @return int|string|null
     */
    protected static function findInArray($needle, array $haystack)
    {
        if (!isset($needle)) {
            return null;
        }

        if (is_object($needle)) {
            if (isset($needle->id)) {
                $needle = $needle->id;
            }
        }

        foreach ($haystack as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }

        return null;
    }

    protected static function getFichePersonneId(object $personne): ?int
    {
        foreach ($personne->fiches as $fiche) {
            if ('Personne' === $fiche->typefiche) {
                return $fiche->id;
            }
        }

        return null;
    }
}
