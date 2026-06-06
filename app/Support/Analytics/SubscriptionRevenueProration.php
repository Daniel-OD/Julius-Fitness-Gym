<?php

namespace App\Support\Analytics;

use Carbon\CarbonImmutable;

final class SubscriptionRevenueProration
{
    public function inclusiveDayCount(CarbonImmutable $start, CarbonImmutable $end): int
    {
        return max($start->diffInDays($end) + 1, 1);
    }

    public function overlapDayCount(
        CarbonImmutable $subscriptionStart,
        CarbonImmutable $subscriptionEnd,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
    ): int {
        $overlapStart = $subscriptionStart->greaterThan($rangeStart) ? $subscriptionStart : $rangeStart;
        $overlapEnd = $subscriptionEnd->lessThan($rangeEnd) ? $subscriptionEnd : $rangeEnd;

        if ($overlapStart->greaterThan($overlapEnd)) {
            return 0;
        }

        return $this->inclusiveDayCount($overlapStart, $overlapEnd);
    }

    public function proratedAmount(
        float $totalAmount,
        CarbonImmutable $subscriptionStart,
        CarbonImmutable $subscriptionEnd,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
    ): float {
        if ($totalAmount <= 0) {
            return 0.0;
        }

        $totalDays = $this->inclusiveDayCount($subscriptionStart, $subscriptionEnd);
        $overlapDays = $this->overlapDayCount(
            $subscriptionStart,
            $subscriptionEnd,
            $rangeStart,
            $rangeEnd,
        );

        if ($overlapDays === 0) {
            return 0.0;
        }

        return round($totalAmount * ($overlapDays / $totalDays), 2);
    }

    /**
     * @return array<string, float>
     */
    public function dailyBreakdown(
        float $totalAmount,
        CarbonImmutable $subscriptionStart,
        CarbonImmutable $subscriptionEnd,
        CarbonImmutable $rangeStart,
        CarbonImmutable $rangeEnd,
    ): array {
        if ($totalAmount <= 0) {
            return [];
        }

        $totalDays = $this->inclusiveDayCount($subscriptionStart, $subscriptionEnd);
        $dailyRate = $totalAmount / $totalDays;
        $breakdown = [];

        $day = $subscriptionStart->max($rangeStart)->startOfDay();
        $lastDay = $subscriptionEnd->min($rangeEnd)->startOfDay();

        while ($day->lessThanOrEqualTo($lastDay)) {
            $key = $day->toDateString();
            $breakdown[$key] = ($breakdown[$key] ?? 0) + round($dailyRate, 2);
            $day = $day->addDay();
        }

        return $breakdown;
    }
}
