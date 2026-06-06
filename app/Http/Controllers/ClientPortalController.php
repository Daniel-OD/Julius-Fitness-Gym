<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Members\MemberQrCodeService;
use App\Services\Members\MemberSubscriptionAccessService;
use App\Support\AppConfig;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function __construct(
        private readonly MemberQrCodeService $qrCodeService,
        private readonly MemberSubscriptionAccessService $subscriptionAccess,
    ) {}

    public function index(Request $request): View
    {
        $member = $this->resolveLinkedMember($request->user());

        if ($member === null) {
            return view('client.unlinked');
        }

        return view('client.dashboard', $this->portalData($member));
    }

    public function qr(Request $request): View
    {
        $member = $this->resolveLinkedMember($request->user());

        if ($member === null) {
            abort(403);
        }

        return view('client.qr', $this->portalData($member));
    }

    private function resolveLinkedMember(?User $user): ?Member
    {
        if ($user === null) {
            return null;
        }

        return $user->member;
    }

    /**
     * @return array<string, mixed>
     */
    private function portalData(Member $member): array
    {
        $member->ensureCheckinToken();

        return [
            'member' => $member,
            'access' => $this->subscriptionAccess->forMember($member),
            'activeSubscription' => $this->activeSubscription($member),
            'qrSvg' => $this->qrCodeService->svgForMember($member),
            'recentCheckIns' => $member->checkIns()
                ->latest('checked_in_at')
                ->limit(10)
                ->get(),
        ];
    }

    private function activeSubscription(Member $member): ?Subscription
    {
        $today = CarbonImmutable::today(AppConfig::timezone());

        return $member->subscriptions()
            ->with('plan')
            ->whereDate('start_date', '<=', $today->toDateString())
            ->whereDate('end_date', '>=', $today->toDateString())
            ->whereNotIn('status', ['cancelled', 'renewed'])
            ->latest('end_date')
            ->first();
    }
}
