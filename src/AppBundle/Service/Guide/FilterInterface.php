<?php

declare(strict_types=1);

namespace AppBundle\Service\Guide;

interface FilterInterface
{
    public function handle(array $options): array;
}
