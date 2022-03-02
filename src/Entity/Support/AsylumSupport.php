<?php

namespace App\Entity\Support;

class AsylumSupport
{
    public const END_REASONS = [
        100 => 'Accès à une solution d\'hébgt/logt',
        200 => 'Non adhésion à l\'accompagnement',
        210 => 'Exclusion disciplinaire',
        400 => 'Fin de prise en charge OFII',
        410 => 'Transfert Dublin',
        310 => 'Départ volontaire',
        900 => 'Décès',
        97 => 'Autre',
        99 => 'Inconnu',
    ];
}
