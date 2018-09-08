<?php

declare(strict_types=1);

namespace AppBundle\Service\Guide;

use AppBundle\Service\GuideAPIInterface;

class GuideFilter implements FilterInterface
{
    const DAY_MAX_MIN = 750;
    const RELOCATION_MIN = 30;

    /**
     * @var GuideAPIInterface
     */
    private $guideApi;

    /**
     * @param GuideAPIInterface $guideApi
     */
    public function __construct(GuideAPIInterface $guideApi)
    {
        $this->guideApi = $guideApi;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function handle(array $options): array
    {
        $e = [];
        $result = [];
        $data = $this->guideApi->fetch();
        $budget = 0;
        $days = $options['days'] ?: 1;

        for ($i = 1; $i <= $days; ++$i) {
            $cTime = 0;
            $start = new \DateTime('tomorrow');
            $start->setTime(10, 00);
            $start->add(new \DateInterval('P' . $i . 'D'));
            if (isset($result['schedule'])) {
                foreach ($result['schedule'] as $key) {
                    $e = array_merge($e, array_values(array_column($key, 'id')));
                }
            }

            foreach ($data as $item) {
                if ((self::DAY_MAX_MIN >= $cTime &&
                    self::DAY_MAX_MIN >= $cTime + ($item['duration'] + 30)) &&
                    !isset($result['schedule'][$i][$item['id']]) &&
                    $options['budget'] >= $budget + $item['price'] &&
                    !\in_array($item['id'], $e, true)
                ) {
                    $startT = clone $start;
                    $startT->add(new \DateInterval('PT' . $cTime . 'M'));

                    $result['schedule'][$i][] = [
                        'id' => $item['id'],
                        'start' => $startT->format('Y-m-d H:i'),
                        'duration' => $item['duration'],
                    ];
                    $budget += $item['price'];
                    $cTime = $cTime + ($item['duration'] + 30);
                }
            }
        }
        $totalActivities = array_values(array_map('count', $result['schedule']));

        $summary = [
            'Budget Spent' => $budget,
            'Time in relocation' => array_sum($totalActivities) * self::RELOCATION_MIN,
            'Total Activities' => array_sum($totalActivities),
        ];

        return array_merge(['summary' => $summary], $result);
    }
}
