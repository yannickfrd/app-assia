<?php

namespace App\Twig;

use Twig\Extension\RuntimeExtensionInterface;

class EvaluationScoreColor implements RuntimeExtensionInterface
{
    public function getColor(?int $value = null): string
    {
        if ($value >= 95) {
            return 'success';
        }
        if ($value >= 80) {
            return 'secondary';
        }
        if ($value >= 60) {
            return 'warning';
        }

        return 'danger';
    }
}
