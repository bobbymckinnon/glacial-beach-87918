<?php

declare(strict_types=1);

namespace AppBundle\Service\Guide;

use AppBundle\Service\GuideAPIInterface;

class GuideFilter implements FilterInterface
{
    /** DAY_MAX_MIN the max number of minutes per day */
    const DAY_MAX_MIN = 750;

    /** RELOCATION_MIN time allotted between events */
    const RELOCATION_MIN = 30;

    /** DAILY_EVENT_MIN min number of events to be scheduled per day */
    const DAILY_EVENT_MIN = 3;

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
        $data = $this->guideApi->fetch();

        $days = (int) $options['days'] ?: 1;
        $dailyBudget = intdiv((int) $options['budget'], $days);

        if (isset($options['priority'])) {
            $data = self::withPriority($data, $options['priority']);
        }

        $events = $this->buildEventTree($data, $dailyBudget);

        $events = $this->normalizeEvents($events, $days);

        $totalActivities = array_sum(array_column($events, 'total'));
        $totalCosts = array_sum(array_column($events, 'total_price'));

        $result['schedule'] = $events;

        $summary = [
            'budget_spent' => $totalCosts,
            'time_in_relocation' => $totalActivities * self::RELOCATION_MIN,
            'total_activities' => $totalActivities,
        ];

        return array_merge(['summary' => $summary], $result);
    }

    /**
     * @param array $events
     * @param int   $days
     *
     * @return array
     */
    private function normalizeEvents(array $events, int $days): array
    {
        $result = [];
        array_multisort(array_column($events, 'total_price'), SORT_DESC, $events);
        $i = 0;
        foreach ($events as $event) {
            ++$i;
            if ($i <= $days) {
                $result[$i] = $event;
            }
        }

        return $result;
    }

    /**
     * @param array $data
     * @param int   $dailyBudget
     * @param int   $perDay
     *
     * @return array
     */
    private function buildEventTree(array $data, int $dailyBudget, int $perDay = self::DAILY_EVENT_MIN): array
    {
        $result = $eventIds = [];
        $days = 100;

        for ($i = 1; $i <= $days; ++$i) {
            $cTime = $budget = 0;
            $eventGroup = [];
            $start = new \DateTime('tomorrow');
            $start->setTime(10, 00);
            $start->add(new \DateInterval('P' . $i . 'D'));

            foreach ($result as $key) {
                $eventIds = array_merge($eventIds, array_values(array_column($key, 'id')));
            }

            foreach ($data as $item) {
                if ((self::DAY_MAX_MIN >= $cTime && self::DAY_MAX_MIN >= $cTime + ($item['duration'] + self::RELOCATION_MIN)) &&
                    !isset($result[$i][$item['id']]) &&
                    $dailyBudget >= $budget + $item['price'] &&
                    !\in_array($item['id'], $eventIds, true)
                ) {
                    $startT = clone $start;
                    $startT->add(new \DateInterval('PT' . $cTime . 'M'));
                    $eventKey = $startT->format('H:i:s');

                    $eventGroup[$eventKey] = [
                        'id' => $item['id'],
                        'duration' => $item['duration'],
                        'price' => $item['price'],
                    ];
                    $cTime = $cTime + ($item['duration'] + self::RELOCATION_MIN);
                    $budget += $item['price'];
                }

                if (\count($eventGroup) >= $perDay) {
                    $eventGroup['total_price'] = self::eventGroupPrice($eventGroup);
                    $eventGroup['total_duration'] = self::eventGroupDuration($eventGroup);
                    $eventGroup['total'] = self::eventGroupTotal($eventGroup);

                    $result[$i] = $eventGroup;
                }
            }
        }

        return $result;
    }

    /**
     * @param array  $data
     * @param string $col
     *
     * @return array
     */
    private static function withPriority(array $data, string $col): array
    {
        if (!\in_array($col, ['price', 'duration'], true)) {
            return $data;
        }

        usort($data, function ($a, $b) use ($col) {
            return $a[$col] - $b[$col];
        });

        return $data;
    }

    /**
     * @param array $eventGroup
     *
     * @return int
     */
    private static function eventGroupPrice(array $eventGroup): int
    {
        return array_sum(array_column($eventGroup, 'price'));
    }

    /**
     * @param array $eventGroup
     *
     * @return int
     */
    private static function eventGroupDuration(array $eventGroup): int
    {
        return array_sum(array_column($eventGroup, 'duration'));
    }

    /**
     * @param array $eventGroup
     *
     * @return int
     */
    private static function eventGroupTotal(array $eventGroup): int
    {
        return \count(array_column($eventGroup, 'id'));
    }
}
