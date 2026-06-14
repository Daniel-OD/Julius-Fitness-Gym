<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the legacy `member_checkins` table. Superseded by `check_ins`; the
 * MemberCheckin model and any writers were already removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('member_checkins');
    }

    public function down(): void
    {
        Schema::create('member_checkins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_in_at');
            $table->timestamps();

            $table->index(['checked_in_at', 'member_id']);
        });
    }
};
