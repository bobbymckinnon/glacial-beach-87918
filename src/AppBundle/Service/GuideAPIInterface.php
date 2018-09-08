<?php

declare(strict_types=1);

namespace AppBundle\Service;

interface GuideAPIInterface
{
    /**
     * @return array
     */
    public function fetch(): array;
}
