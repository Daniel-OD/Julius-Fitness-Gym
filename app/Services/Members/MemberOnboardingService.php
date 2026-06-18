<?php

namespace App\Services\Members;

use App\Helpers\Helpers;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Support\AppConfig;
use App\Support\Billing\PaymentMethod;
use App\Support\Data;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MemberOnboardingService
{
    /**
     * Create a member, subscription and invoice from wizard form data and
     * update the source enquiry to status 'member'.
     *
     * Member status is intentionally left to the SubscriptionObserver —
     * it calls MemberStatusSyncService after every Subscription save.
     *
     * @param  array<string, mixed>  $data  Flat form data from the onboarding wizard
     */
    public function createFromEnquiry(Enquiry $enquiry, array $data): Member
    {
        return DB::transaction(function () use ($enquiry, $data): Member {
            $member = Member::create([
                'name' => Data::string($data['name']),
                'email' => Data::nullableString($data['email'] ?? null),
                'contact' => Data::string($data['contact']),
                'dob' => $data['dob'] ?? null,
                'gender' => Data::nullableString($data['gender'] ?? null),
                'address' => Data::nullableString($data['address'] ?? null),
                'country' => Data::nullableString($data['country'] ?? null),
                'state' => Data::nullableString($data['state'] ?? null),
                'city' => Data::nullableString($data['city'] ?? null),
                'pincode' => Data::nullableString($data['pincode'] ?? null),
                'source' => Data::nullableString($data['source'] ?? null),
                'goal' => Data::nullableString($data['goal'] ?? null),
            ]);

            $today = Carbon::today(AppConfig::timezone());
            $planId = Data::int($data['plan_id']);
            $startDate = Carbon::parse(Data::string($data['start_date']))->toDateString();
            $endDate = Data::nullableString($data['end_date'] ?? null)
                ?: Helpers::calculateSubscriptionEndDate($startDate, $planId);

            $subscription = Subscription::create([
                'member_id' => $member->id,
                'plan_id' => $planId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => Carbon::parse($startDate)->gt($today) ? 'upcoming' : 'ongoing',
            ]);

            $plan = Plan::findOrFail($planId);
            $paymentMethod = Data::nullableString($data['payment_method'] ?? null);
            $paidAmount = PaymentMethod::isOnline($paymentMethod)
                ? 0.0
                : max(Data::float($data['paid_amount'] ?? 0), 0.0);

            $invoiceDate = Carbon::parse(Data::string($data['invoice_date']))->toDateString();
            $invoiceDueDate = Carbon::parse(
                Data::string($data['invoice_due_date'] ?? $invoiceDate)
            )->toDateString();

            Invoice::create([
                'subscription_id' => $subscription->id,
                'number' => Data::nullableString($data['invoice_number'] ?? null) ?: null,
                'date' => $invoiceDate,
                'due_date' => $invoiceDueDate,
                'payment_method' => $paymentMethod,
                'discount' => Data::int($data['discount'] ?? 0) ?: null,
                'discount_amount' => max(Data::float($data['discount_amount'] ?? 0), 0.0) ?: null,
                'discount_note' => Data::nullableString($data['discount_note'] ?? null),
                'paid_amount' => $paidAmount,
                'subscription_fee' => round((float) $plan->amount, 2),
                'status' => 'issued',
            ]);

            $enquiry->update(['status' => 'member']);

            return $member;
        });
    }
}
