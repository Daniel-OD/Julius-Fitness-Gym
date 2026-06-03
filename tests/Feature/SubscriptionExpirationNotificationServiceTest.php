<?php

use App\Helpers\Helpers;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Subscriptions\SubscriptionExpirationNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $today = CarbonImmutable::parse('2026-06-02', config('app.timezone'))->startOfDay();
    CarbonImmutable::setTestNow($today);

    Helpers::setTestSettingsOverride([
        'subscriptions' => [
            'expiring_days' => 7,
        ],
    ]);
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
    Helpers::setTestSettingsOverride(null);
});

it('tracks unread expiring subscriptions and marks them as read', function (): void {
    $user = User::factory()->create();
    $member = Member::factory()->create(['name' => 'Alex Pop']);
    $plan = Plan::factory()->create(['name' => 'Monthly']);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-05',
        'status' => 'expiring',
    ]);

    $service = app(SubscriptionExpirationNotificationService::class);

    expect($service->getUnreadCount($user))->toBe(1);

    $items = $service->getItemsForUser($user);

    expect($items)->toHaveCount(1)
        ->and($items->first()->memberName)->toBe('Alex Pop')
        ->and($items->first()->planName)->toBe('Monthly')
        ->and($items->first()->daysLeft)->toBe(3)
        ->and($items->first()->urgency)->toBe('danger')
        ->and($items->first()->isRead)->toBeFalse();

    $service->markAllAsRead($user);

    expect($service->getUnreadCount($user))->toBe(0)
        ->and($service->getItemsForUser($user)->first()->isRead)->toBeTrue();
});

it('assigns urgency levels by days remaining', function (): void {
    $user = User::factory()->create();
    $member = Member::factory()->create();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-09',
        'status' => 'expiring',
    ]);

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => '2026-01-01',
        'end_date' => '2026-06-02',
        'status' => 'expiring',
    ]);

    $items = app(SubscriptionExpirationNotificationService::class)->getItemsForUser($user);

    expect($items->firstWhere('daysLeft', 7)?->urgency)->toBe('warning')
        ->and($items->firstWhere('daysLeft', 0)?->urgency)->toBe('critical');
});
