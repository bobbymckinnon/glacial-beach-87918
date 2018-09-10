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
        $result = [];
        $tourIds = [];
        $data = $this->guideApi->fetch();

        $days = (int) $options['days'] ?: 1;
        $dailyBudget = intdiv((int) $options['budget'], $days);
        $budgetSpent = 0;

        for ($i = 1; $i <= $days; ++$i) {
            $budget = 0;
            $cTime = 0;
            $eventSet = [];
            $start = new \DateTime('tomorrow');
            $start->setTime(10, 00);
            $start->add(new \DateInterval('P' . $i . 'D'));

            if (isset($result['schedule'])) {
                foreach ($result['schedule'] as $key) {
                    $tourIds = array_merge($tourIds, array_values(array_column($key, 'id')));
                }
            } else {
                $result['schedule'] = [];
            }

            foreach ($data as $item) {
                if ((self::DAY_MAX_MIN >= $cTime &&
                    self::DAY_MAX_MIN >= $cTime + ($item['duration'] + 30)) &&
                    !isset($result['schedule'][$i][$item['id']]) &&
                    $dailyBudget >= $budget + $item['price'] &&
                    !\in_array($item['id'], $tourIds, true)
                ) {
                    $startT = clone $start;
                    $startT->add(new \DateInterval('PT' . $cTime . 'M'));
                    $eventKey = $startT->format('H:i:s');

                    $eventSet[$eventKey] = [
                        'id' => $item['id'],
                        'duration' => $item['duration'],
                        'price' => $item['price'],
                    ];
                    $budget += $item['price'];
                    $cTime = $cTime + ($item['duration'] + 30);
                }

                if (count($eventSet) >= 3) {
                    $result['schedule'][$i] = $eventSet;
                }
            }
            $budgetSpent += $budget;
        }
        $totalActivities = array_values(array_map('count', $result['schedule']));

        $summary = [
            'budget_spent' => $budgetSpent,
            'time_in_relocation' => array_sum($totalActivities) * self::RELOCATION_MIN,
            'total_activities' => array_sum($totalActivities),
            'cc' => count($this->buildEventTree($data)),
        ];

        return array_merge(['summary' => $summary], $result);
    }

    public function buildEventTree($data):array {
        $result = [];
        $tourIds = [];

        $days = 100;

        for ($i = 1; $i <= $days; ++$i) {
            $cTime = 0;
            $eventGroup = [];
            $start = new \DateTime('tomorrow');
            $start->setTime(10, 00);
            $start->add(new \DateInterval('P' . $i . 'D'));

            foreach ($result as $key) {
                $tourIds = array_merge($tourIds, array_values(array_column($key, 'id')));
            }

            foreach ($data as $item) {
                if ((self::DAY_MAX_MIN >= $cTime && self::DAY_MAX_MIN >= $cTime + ($item['duration'] + 30)) &&
                    !isset($result[$i][$item['id']]) &&
                    !\in_array($item['id'], $tourIds, true)
                ) {
                    $startT = clone $start;
                    $startT->add(new \DateInterval('PT' . $cTime . 'M'));
                    $eventKey = $startT->format('H:i:s');

                    $eventGroup[$eventKey] = [
                        'id' => $item['id'],
                        'duration' => $item['duration'],
                        'price' => $item['price'],
                    ];
                    $cTime = $cTime + ($item['duration'] + 30);
                }

                if (count($eventGroup) >= 3) {
                    $eventGroup['total_price'] = self::eventGroupPrice($eventGroup);
                    $eventGroup['total_duration'] = self::eventGroupDuration($eventGroup);

                    $result[$i] = $eventGroup;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $eventGroup
     * @return int
     */
    private static function eventGroupPrice(array $eventGroup): int
    {
        return array_sum(array_column($eventGroup, 'price'));
    }

    /**
     * @param array $eventGroup
     * @return int
     */
    private static function eventGroupDuration(array $eventGroup): int
    {
        return array_sum(array_column($eventGroup, 'duration'));
    }
}
