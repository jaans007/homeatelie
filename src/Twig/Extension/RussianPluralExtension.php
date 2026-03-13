<?php

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RussianPluralExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('ru_plural', [$this, 'ruPlural']),
        ];
    }

    public function ruPlural(int $number, string $one, string $two, string $many): string
    {
        $mod10 = $number % 10;
        $mod100 = $number % 100;

        if ($mod10 === 1 && $mod100 !== 11) {
            return $one;
        }

        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 10 || $mod100 >= 20)) {
            return $two;
        }

        return $many;
    }
}
