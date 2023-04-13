<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\MorphExtensionRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MorphExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('morph', [MorphExtensionRuntime::class, 'wordForm']),
        ];
    }

}
