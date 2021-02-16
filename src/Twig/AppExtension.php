<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

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

    public function getFunctions()
    {
        return [
            new TwigFunction('ratio', [$this, 'ratio']),
            new TwigFunction('alert_color', [$this, 'alertColor']),
        ];
    }

    public function formatNumber($value, int $decimals = 0): string
    {
        return number_format($value, $decimals, ',', ' ');
    }

    public function formatPercentage($value, int $decimals = 2): string
    {
        $value = round($value, $decimals);

        // if ((string) (int) $value === (string) $value) {
        //     $decimals = 0;
        // }

        return number_format($value, $decimals, ',', ' ').'%';
    }

    public function formatPrice($value, int $decimals = 2): string
    {
        return number_format($value, $decimals, ',', ' ').' €';
    }

    public function roundNumber($value, int $decimals = 0, $mode = 1): string
    {
        $value = round($value, $decimals, $mode);

        if ((string) (int) $value === (string) $value) {
            $decimals = 0;
        }

        return number_format($value, $decimals, ',', ' ');
    }

    public function ratio(int $value1, int $value2): ?float
    {
        if (0 === $value2) {
            return null;
        }

        return ($value1 / $value2) * 100;
    }

    public function alertColor($value): string
    {
        if ($value > 150) {
            return 'alert-danger';
        }
        if ($value > 100) {
            return 'alert-warning';
        }
        if ($value >= 95) {
            return 'alert-success';
        }
        if ($value >= 80) {
            return 'alert-info';
        }
        if ($value >= 60) {
            return 'alert-warning';
        }

        return 'alert-danger';
    }
}
