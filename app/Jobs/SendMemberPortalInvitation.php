<?php

namespace App\Jobs;

use App\Contracts\SettingsRepository;
use App\Mail\MemberPortalInvitationMail;
use App\Models\Member;
use App\Support\AppConfig;
use App\Support\Data;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Send a portal invitation email so a member can set their password.
 */
class SendMemberPortalInvitation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $memberId,
        public readonly ?int $actorId = null,
    ) {}

    public function handle(SettingsRepository $settingsRepository): void
    {
        $member = Member::query()->find($this->memberId);

        if (! $member instanceof Member) {
            return;
        }

        $memberEmail = Data::string($member->email);

        if (! filter_var($memberEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info('Skipping portal invitation: member email missing.', [
                'member_id' => $this->memberId,
            ]);

            return;
        }

        $settings = $settingsRepository->get();
        $gymName = Data::string(data_get($settings, 'general.gym_name', AppConfig::string('app.name')));
        $gymEmail = Data::string(data_get($settings, 'general.gym_email', ''));
        $gymContact = Data::string(data_get($settings, 'general.gym_contact', ''));
        $memberName = Data::string($member->name);

        $setPasswordUrl = route('member.set-password', ['email' => $memberEmail]);

        $mailable = new MemberPortalInvitationMail(
            subjectLine: Data::string(__('app.emails.portal_invitation_subject', ['gym' => $gymName ?: 'Julius Fitness Gym'])),
            gymName: $gymName ?: 'Julius Fitness Gym',
            gymEmail: $gymEmail,
            gymContact: $gymContact,
            memberName: $memberName,
            setPasswordUrl: $setPasswordUrl,
        );

        if (filled($gymEmail)) {
            $mailable->replyTo($gymEmail, $gymName);
        }

        Mail::to($memberEmail)->send($mailable);
    }
}
