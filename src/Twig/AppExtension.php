<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('number', [$this, 'formatNumber']),
            new TwigFilter('percent', [$this, 'formatPercentage']),
            new TwigFilter('amount', [$this, 'formatAmount']),
            new TwigFilter('round', [$this, 'roundNumber']),
            new TwigFilter('cumulate', [$this, 'cumulate']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ratio', [$this, 'getRatio']),
            new TwigFunction('color', [$this, 'getColor']),
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

    public function formatAmount($value, int $decimals = 2): string
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

    /**
     * Filter to cumulate the value of method from objects collection.
     *
     * @param object[] $array
     */
    public function cumulate(?array $array, string $methodName): ?float
    {
        if (!$array) {
            return null;
        }

        $sum = 0;

        foreach ($array as $object) {
            $getMethod = 'get'.$methodName;
            if (method_exists($object, $getMethod)) {
                $sum += $object->$getMethod();
            }
        }

        return $sum;
    }

    public function GetRatio(int $value1, int $value2): ?float
    {
        if (0 === $value2) {
            return null;
        }

        return ($value1 / $value2) * 100;
    }

    public function getColor(?int $value = null): string
    {
        if ($value > 150) {
            return 'danger';
        }
        if ($value > 100) {
            return 'warning';
        }
        if ($value >= 95) {
            return 'success';
        }
        if ($value >= 80) {
            return 'info';
        }
        if ($value >= 60) {
            return 'warning';
        }

        return 'danger';
    }
}
