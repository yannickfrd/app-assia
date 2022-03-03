<?php

namespace App\Service\SiSiao;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SiSiaoGlossary extends SiSiaoClient
{
    public const REFERENTIELS = [
        '/referentiels' => [
            '/situationDemandes',
            '/motifDemandes',
            '/dureeErrances',
            '/compositions',
            '/situationPersonnes',
            '/situationSortieDemande',
            '/papierIdentites',
            '/droitSejours',
            '/typeHebergementEnfant',
            '/regroupementFamilial',
            '/droitOuvertSecuriteSociales',
            '/animaux',
            '/typeContrats',
            '/typeRessources',
            '/typeCharges',
            '/typeDettes',
            '/evolutionsBudgetaires',
            '/dispositif',
            '/mesureAccompagnement',
            '/categoriePlaces',
            '/typePlaces',
            '/typesEtablissementUn',
        ],
    ];

    public function __construct(HttpClientInterface $client, RequestStack $requestStack, string $url)
    {
        parent::__construct($client, $requestStack, $url);
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

        foreach (self::REFERENTIELS['/referentiels'] as $key => $value) {
            $referentiel = $this->get('/referentiels'.$value);
            $referentielsString .= '// '.strtoupper($value).
                '<br/>public const '.strtoupper($value).' = [<br/>';

            foreach ($referentiel as $item) {
                if (isset($item->libelle)) {
                    $referentielsString .= "&nbsp;&nbsp;&nbsp;&nbsp;{$item->id} => null, // {$item->libelle}<br/>";
                    continue;
                }
                if (isset($item->nom)) {
                    $referentielsString .= "&nbsp;&nbsp;&nbsp;&nbsp;{$item->id} => null, // {$item->nom}".
                    (isset($item->dispositif) ? ' - '.$item->dispositif->libelle : null).'<br/>';
                }
            }

            $referentielsString .= '];<br/><br/>';

            $paths[] = $key.$value;
        }

        return $referentielsString;
    }
}
