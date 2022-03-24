<?php

namespace App\Service;

class SoundexFr
{
    /**
     * SOUNDEX FRENCH, Frederic Bouchery,  26-Sep-2003
     * http://www.php-help.net/sources-php/a.french.adapted.soundex.289.html.
     */
    public function get(string $sIn): string
    {
        // Si il n'y a pas de mot, on sort immédiatement
        if ('' === $sIn) {
            return $sIn;
        }
        // On met tout en minuscule
        $sIn = strtoupper($sIn);
        // On supprime les accents
        $sIn = strtr($sIn, 'ÂÄÀÇÈÉÊË&#338;ÎÏÔÖÙÛÜ', 'AAASEEEEEIIOOUUU');
        // On supprime tout ce qui n'est pas une lettre
        $sIn = preg_replace('`[^A-Z]`', '', $sIn);
        // Si la chaîne ne fait qu'un seul caractère, on sort avec.
        if (1 === strlen($sIn)) {
            return $sIn;
        }
        // Remplace les Y par des I
        $sIn = str_replace('Y', 'I', $sIn);
        $sIn = str_replace('QUE', 'C', $sIn);
        // on remplace les consonnances primaires
        $convIn = ['GUI', 'GUE', 'GA', 'GO', 'GU', 'CA', 'CO', 'CU', 'Q', 'CC', 'CK'];
        $convOut = ['KI', 'KE', 'KA', 'KO', 'K', 'KA', 'KO', 'KU', 'K', 'K', 'K'];
        $sIn = str_replace($convIn, $convOut, $sIn);
        // on remplace les voyelles sauf le Y et sauf la première par A
        $sIn = preg_replace('`(?<!^)[EIOU]`', 'A', $sIn);
        // on remplace les préfixes puis on conserve la première lettre
        // et on fait les remplacements complémentaires
        $convIn = ['`^KN`', '`^(PH|PF)`', '`^MAC`', '`^SCH`', '`^ASA`',
            '`(?<!^)KN`', '`(?<!^)(PH|PF)`', '`(?<!^)MAC`', '`(?<!^)SCH`',
            '`(?<!^)ASA`', ];
        $convOut = ['NN', 'FF', 'MCC', 'SSS', 'AZA', 'NN', 'FF', 'MCC', 'SSS', 'AZA'];
        $sIn = preg_replace($convIn, $convOut, $sIn);

        // suppression des H sauf CH ou SH
        $sIn = preg_replace('`(?<![CS])H`', '', $sIn);
        // suppression des Y sauf précédés d'un A
        $sIn = preg_replace('`(?<!A)Y`', '', $sIn);
        // on supprime les terminaisons A, T, D, S
        $sIn = preg_replace('`[ATDS]$`', '', $sIn);
        // suppression de tous les A sauf en tête
        $sIn = preg_replace('`(?!^)A`', '', $sIn);
        // on supprime les lettres répétitives
        $sIn = preg_replace('`(.)\1`', '$1', $sIn);

        // on ne retient que 6 caractères
        return substr($sIn, 0, 6);
    }

    /**
     * Utilisation du Soundex original en corrigeant les biais dûs à la langue française.
     */
    public function get2(string $str): string
    {
        $str = strtoupper($str);
        $str = str_replace('H', '', $str);
        $str = str_replace('K', 'C', $str);
        $str = str_replace('Y', 'I', $str);

        return soundex($str);
    }
}
