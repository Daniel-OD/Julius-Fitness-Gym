<?php

namespace App\Jobs;

use App\Models\Member;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsAppWelcome implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $memberId) {}

    public function handle(WhatsAppService $whatsApp): void
    {
        $member = Member::find($this->memberId);

        if (! $member) {
            return;
        }

        $whatsApp->sendWelcome($member);
    }
}
