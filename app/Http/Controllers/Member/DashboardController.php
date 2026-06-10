<?php

namespace App\Http\Controllers\Member;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Member;
use App\Services\Members\MemberPlanSelectionService;
use App\Services\Members\MemberQrCodeService;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private MemberPlanSelectionService $planSelection) {}

    public function index(MemberQrCodeService $qrCodeService): View|RedirectResponse
    {
        /** @var Member $member */
        $member = auth('member')->user();

        $this->planSelection->fulfillPending($member);

        if (! $member->subscriptions()->exists()) {
            return redirect()->route('member.plans');
        }

        $today = CarbonImmutable::today(AppConfig::timezone());

        $activeSubscription = $member->subscriptions()
            ->with('plan')
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->whereNotIn('status', [
                Status::Cancelled->value,
                Status::Renewed->value,
                Status::Expired->value,
                Status::PendingPayment->value,
            ])
            ->orderByDesc('end_date')
            ->first();

        $daysRemaining = null;
        $subscriptionBadgeTone = null;

        if ($activeSubscription !== null) {
            $daysRemaining = (int) $today->diffInDays(
                CarbonImmutable::parse($activeSubscription->end_date, AppConfig::timezone())->startOfDay(),
                false,
            );

            $subscriptionBadgeTone = match (true) {
                $daysRemaining > 7 => 'green',
                $daysRemaining >= 3 => 'orange',
                default => 'red',
            };
        }

        $member->ensureCheckinToken();
        $qrSvg = $qrCodeService->svgForMember($member);

        $pendingPaymentSubscription = $member->subscriptions()
            ->with('plan')
            ->where('status', Status::PendingPayment->value)
            ->latest()
            ->first();

        $invoices = Invoice::query()
            ->whereHas('subscription', fn ($query) => $query->where('member_id', $member->id))
            ->orderByDesc('date')
            ->limit(10)
            ->get();

        return view('member.dashboard.index', [
            'member' => $member,
            'activeSubscription' => $activeSubscription,
            'pendingPaymentSubscription' => $pendingPaymentSubscription,
            'daysRemaining' => $daysRemaining,
            'subscriptionBadgeTone' => $subscriptionBadgeTone,
            'qrSvg' => $qrSvg,
            'invoices' => $invoices,
        ]);
    }
}
