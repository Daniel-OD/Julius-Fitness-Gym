<?php

use App\Enums\Status;
use App\Jobs\SendNewMemberNotification;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\MemberPlanIntent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

it('stores intended plan when visiting register with plan query', function (): void {
    $plan = Plan::factory()->create(['status' => Status::Active]);

    $this->get(route('member.register', ['plan' => $plan->id]))
        ->assertOk()
        ->assertSessionHas(MemberPlanIntent::SESSION_KEY, $plan->id);
});

it('shows active plans from database on homepage', function (): void {
    $plan = Plan::factory()->create([
        'status' => Status::Active,
        'name' => 'Lunar',
        'amount' => 150,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Lunar')
        ->assertSee(route('member.register', ['plan' => $plan->id]), false);
});

it('creates subscription and invoice after email verification when plan was intended', function (): void {
    Queue::fake();

    $plan = Plan::factory()->create(['status' => Status::Active, 'days' => 30, 'amount' => 150]);

    $this->get(route('member.register', ['plan' => $plan->id]));

    $this->post(route('member.register'), [
        'name' => 'Test Member',
        'email' => 'intent@example.com',
        'contact' => '0712345678',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect(route('member.verify-email'));

    $member = Member::where('email', 'intent@example.com')->firstOrFail();

    $url = URL::temporarySignedRoute(
        'member.verification.verify',
        now()->addMinutes(60),
        ['id' => $member->id, 'hash' => sha1((string) $member->email)]
    );

    $this->actingAs($member, 'member')
        ->get($url)
        ->assertRedirect(route('member.dashboard'))
        ->assertSessionHas('success');

    $subscription = Subscription::where('member_id', $member->id)->first();

    expect($subscription)->not->toBeNull()
        ->and($subscription->plan_id)->toBe($plan->id)
        ->and($subscription->status)->toBe(Status::PendingPayment);

    expect(Invoice::where('subscription_id', $subscription->id)->exists())->toBeTrue();

    Queue::assertPushed(SendNewMemberNotification::class);
});

it('shows pending payment instructions on dashboard after auto plan selection', function (): void {
    $member = Member::factory()->create([
        'password' => 'password',
        'email_verified_at' => now(),
    ]);

    $plan = Plan::factory()->create(['status' => Status::Active, 'name' => 'Lunar']);

    $this->withSession([MemberPlanIntent::SESSION_KEY => $plan->id])
        ->actingAs($member, 'member')
        ->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee(__('app.member.plans.pending_payment_title'))
        ->assertSee(__('app.member.plans.pay_at_reception'));
});
