<?php

declare(strict_types=1);

namespace AppBundle\Service;

class GuideAPI implements GuideAPIInterface
{
    /** @var string */
    private $endpoint = 'https://pastebin.com/raw/KRqTkzMA';

    /**
     * @throws \RuntimeException
     *
     * @return array
     */
    public function fetch(): array
    {
        $json = file_get_contents($this->endpoint);

        $data = json_decode($json, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('Unable to parse response body into JSON: ' . json_last_error());
        }

        return $data;
    }
}
