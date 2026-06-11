<?php

use App\Contracts\SettingsRepository;
use App\Jobs\SendSubscriptionExpiringEmail;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Notifications\SubscriptionExpiringNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function makeExpiringSubscription(int $daysLeft): Subscription
{
    $member = Member::factory()->create([
        'name' => 'Alex Member',
        'email' => 'alex@example.com',
        'status' => 'active',
    ]);

    $plan = Plan::factory()->create([
        'name' => 'Gold Plan',
        'days' => 30,
        'status' => 'active',
    ]);

    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(23)->toDateString(),
        'end_date' => Carbon::today()->addDays($daysLeft)->toDateString(),
        'status' => 'ongoing',
    ])->load(['member', 'plan']);
}

it('can be instantiated with subscription and daysLeft', function (): void {
    $subscription = makeExpiringSubscription(7);

    $notification = new SubscriptionExpiringNotification($subscription, 7);

    expect($notification->subscription->id)->toBe($subscription->id)
        ->and($notification->daysLeft)->toBe(7);
});

it('toMail returns a MailMessage with the correct subject', function (): void {
    app(SettingsRepository::class)->put([
        ...app(SettingsRepository::class)->get(),
        'general' => [
            ...app(SettingsRepository::class)->get()['general'],
            'gym_name' => 'Julius Fitness Gym',
        ],
    ]);

    $subscription = makeExpiringSubscription(7);
    $member = $subscription->member;

    $notification = new SubscriptionExpiringNotification($subscription, 7);
    $mail = $notification->toMail($member);

    expect($mail)->toBeInstanceOf(MailMessage::class)
        ->and($mail->subject)->toBe(__('notifications.subscription_expiring.subject', [
            'gym' => 'Julius Fitness Gym',
            'days' => 7,
        ]));
});

it('sends the notification when the job is handled', function (): void {
    Notification::fake();

    $subscription = makeExpiringSubscription(3);
    $member = $subscription->member;

    $job = new SendSubscriptionExpiringEmail($member, $subscription, 3);
    $job->handle();

    Notification::assertSentTo(
        $member,
        SubscriptionExpiringNotification::class,
        fn (SubscriptionExpiringNotification $notification): bool => $notification->daysLeft === 3
            && $notification->subscription->id === $subscription->id,
    );
});
