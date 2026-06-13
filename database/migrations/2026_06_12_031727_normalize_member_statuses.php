<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Data fix: members imported from older databases can carry a NULL or
 * unknown status, which makes them invisible in the active/inactive
 * filtered lists. Re-bucket those rows from their subscriptions: active
 * when a subscription is valid today, inactive otherwise.
 */
return new class extends Migration
{
    public function up(): void
    {
        $today = now()->toDateString();

        $currentSubscription = function (Builder $query) use ($today): void {
            $query->select(DB::raw(1))
                ->from('subscriptions')
                ->whereColumn('subscriptions.member_id', 'members.id')
                ->whereNull('subscriptions.deleted_at')
                ->whereDate('subscriptions.start_date', '<=', $today)
                ->whereDate('subscriptions.end_date', '>=', $today)
                ->whereNotIn('subscriptions.status', ['cancelled', 'renewed', 'pending_payment', 'expired']);
        };

        $unbucketed = fn (): Builder => DB::table('members')
            ->where(function (Builder $query): void {
                $query->whereNull('status')
                    ->orWhereNotIn('status', ['active', 'inactive']);
            });

        $unbucketed()->whereExists($currentSubscription)->update(['status' => 'active']);
        $unbucketed()->whereNotExists($currentSubscription)->update(['status' => 'inactive']);
    }

    /**
     * Data normalization is intentionally irreversible — the original
     * NULL/unknown values carry no information worth restoring.
     */
    public function down(): void {}
};
