<?php

use App\Filament\Widgets\Office\OfficeExpiringSoonWidget;
use App\Filament\Widgets\Office\OfficePresentNowWidget;
use App\Models\CheckIn;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CheckIns\CheckInService;
use Carbon\CarbonImmutable;
use Database\Seeders\EmployeeRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

function presentNowIds(): Collection
{
    return app(CheckInService::class)->presentNowQuery()->pluck('id');
}

uses(RefreshDatabase::class);

function employee(): User
{
    (new EmployeeRoleSeeder)->run();
    $user = User::factory()->create();
    $user->assignRole('employee');

    return $user;
}

function subscriptionEndingIn(int $days): Subscription
{
    $member = Member::factory()->create();
    $plan = Plan::factory()->create(['days' => 30, 'status' => 'active']);

    return Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
        'start_date' => CarbonImmutable::today()->subDays(20)->toDateString(),
        'end_date' => CarbonImmutable::today()->addDays($days)->toDateString(),
        'status' => 'ongoing',
    ]);
}

// ─── Present now widget ───────────────────────────────────────────────────────

it('present-now returns members checked in today without checkout', function (): void {
    $member = Member::factory()->create();
    $open = CheckIn::factory()->create([
        'member_id' => $member->id,
        'checked_in_at' => now(),
        'checked_out_at' => null,
    ]);

    expect(presentNowIds())->toContain($open->id);
});

it('present-now still shows members who left within the grace window', function (): void {
    $grace = app(CheckInService::class)->presentNowGraceMinutes();
    $recentlyLeft = CheckIn::factory()->create([
        'checked_in_at' => now()->subHour(),
        'checked_out_at' => now()->subMinutes(max($grace - 1, 0)),
it('present-now widget is registered on the office dashboard', function (): void {
    actingAs(employee());

    get('/office')
        ->assertOk()
        ->assertSeeLivewire(OfficePresentNowWidget::class);
});

// ─── Expiring soon widget ─────────────────────────────────────────────────────

it('expiring-soon widget includes subscriptions ending within 1-7 days', function (): void {
    $in3 = subscriptionEndingIn(3);

    $ids = (new OfficeExpiringSoonWidget)->getExpiringSoonQuery()->pluck('id');

    expect($ids)->toContain($in3->id);
});

it('expiring-soon widget excludes subscriptions ending today or beyond 7 days', function (): void {
    $today = subscriptionEndingIn(0);
    $in10 = subscriptionEndingIn(10);

    $ids = (new OfficeExpiringSoonWidget)->getExpiringSoonQuery()->pluck('id');

    expect($ids)->not->toContain($today->id)
        ->and($ids)->not->toContain($in10->id);
});

it('expiring-soon badge color follows the day thresholds', function (): void {
    expect(OfficeExpiringSoonWidget::daysLeftColor(1))->toBe('danger')
        ->and(OfficeExpiringSoonWidget::daysLeftColor(2))->toBe('danger')
        ->and(OfficeExpiringSoonWidget::daysLeftColor(3))->toBe('warning')
        ->and(OfficeExpiringSoonWidget::daysLeftColor(5))->toBe('warning')
        ->and(OfficeExpiringSoonWidget::daysLeftColor(6))->toBe('gray')
        ->and(OfficeExpiringSoonWidget::daysLeftColor(7))->toBe('gray');
});

// ─── Security: employee cannot manage ─────────────────────────────────────────

it('employee gets 404 hitting a member edit url on the office panel', function (): void {
    actingAs(employee());
    $member = Member::factory()->create();

    get("/office/members/{$member->id}/edit")->assertNotFound();
});

it('employee is denied member update, create and delete by policy', function (): void {
    foreach (['ViewAny:Member', 'Update:Member', 'Create:Member', 'Delete:Member'] as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    $employee = employee();
    $member = Member::factory()->create();

    expect($employee->can('update', $member))->toBeFalse()
        ->and($employee->can('create', Member::class))->toBeFalse()
        ->and($employee->can('delete', $member))->toBeFalse();
});

it('employee is denied invoice update, create and delete by policy', function (): void {
    foreach (['ViewAny:Invoice', 'Update:Invoice', 'Create:Invoice', 'Delete:Invoice'] as $p) {
        Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
    }

    $employee = employee();
    $member = Member::factory()->create();
    $plan = Plan::factory()->create();
    $subscription = Subscription::factory()->create([
        'member_id' => $member->id,
        'plan_id' => $plan->id,
    ]);
    $invoice = Invoice::factory()->create(['subscription_id' => $subscription->id]);

    expect($employee->can('update', $invoice))->toBeFalse()
        ->and($employee->can('create', Invoice::class))->toBeFalse()
        ->and($employee->can('delete', $invoice))->toBeFalse();
});
