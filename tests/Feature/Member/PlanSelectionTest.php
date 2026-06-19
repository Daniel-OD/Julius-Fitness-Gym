<?php

use App\Enums\Status;
use App\Jobs\SendNewMemberNotification;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function verifiedMemberWithoutSubscription(): Member
{
    return Member::factory()->create([
        'password' => 'password',
        'email_verified_at' => now(),
    ]);
}

it('redirects verified member without subscription from dashboard to plans', function (): void {
    $member = verifiedMemberWithoutSubscription();

    $this->actingAs($member, 'member')
        ->get(route('member.dashboard'))
        ->assertRedirect(route('member.plans'));
});

it('creates subscription and invoice when selecting an active plan', function (): void {
    $member = verifiedMemberWithoutSubscription();
    $plan = Plan::factory()->create(['status' => Status::Active, 'days' => 30, 'amount' => 150]);

    $this->actingAs($member, 'member')
        ->post(route('member.plans.select', $plan))
        ->assertRedirect(route('member.dashboard'))
        ->assertSessionHas('success');

    $subscription = Subscription::where('member_id', $member->id)->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->status)->toBe(Status::PendingPayment)
        ->and($subscription->start_date->toDateString())->toBe(Carbon::today()->toDateString())
        ->and($subscription->end_date->toDateString())->toBe(Carbon::today()->addDays(30)->toDateString());

    $invoice = Invoice::where('subscription_id', $subscription->id)->first();

    expect($invoice)->not->toBeNull()
        ->and((float) $invoice->subscription_fee)->toBe(150.0)
        ->and($invoice->status)->toBe(Status::Issued);
});

it('returns 404 when selecting an inactive plan', function (): void {
    $member = verifiedMemberWithoutSubscription();
    $plan = Plan::factory()->create(['status' => Status::Inactive]);

    $this->actingAs($member, 'member')
        ->post(route('member.plans.select', $plan))
        ->assertNotFound();

    expect(Subscription::where('member_id', $member->id)->exists())->toBeFalse();
});

it('does not redirect member with subscription to plans', function (): void {
    $member = verifiedMemberWithoutSubscription();
    $plan = Plan::factory()->create();

    Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => Carbon::today()->subDays(5),
        'end_date' => Carbon::today()->addDays(25),
        'status' => 'ongoing',
    ]);

    $this->actingAs($member, 'member')
        ->get(route('member.dashboard'))
        ->assertOk()
        ->assertViewIs('member.dashboard.index');
});

it('dispatches SendNewMemberNotification after plan selection', function (): void {
    Queue::fake();

    $member = verifiedMemberWithoutSubscription();
    $plan = Plan::factory()->create(['status' => Status::Active]);

    $this->actingAs($member, 'member')
        ->post(route('member.plans.select', $plan))
        ->assertRedirect(route('member.dashboard'));

    Queue::assertPushed(SendNewMemberNotification::class, fn ($job): bool => $job->memberId === $member->id && $job->planId === $plan->id);
});
