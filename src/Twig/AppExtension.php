<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('number', [$this, 'formatNumber']),
            new TwigFilter('percent', [$this, 'formatPercentage']),
            new TwigFilter('price', [$this, 'formatPrice']),
            new TwigFilter('round', [$this, 'roundNumber']),
        ];
    }

    public function formatNumber($value, int $decimals = 0)
    {
        return number_format($value, $decimals, ',', ' ');
    }

    public function formatPercentage($value, int $decimals = 2)
    {
        $value = round($value, $decimals);

        if ((string) (int) $value === (string) $value) {
            $decimals = 0;
        }

        return number_format($value, $decimals, ',', ' ').'%';
    }

    public function formatPrice($value, int $decimals = 2)
    {
        return number_format($value, $decimals, ',', ' ').' €';
    }

    public function roundNumber($value, int $decimals = 0, $mode = 1)
    {
        $value = round($value, $decimals, $mode);

        if ((string) (int) $value === (string) $value) {
            $decimals = 0;
        }

        return number_format($value, $decimals, ',', ' ');
    }
}
