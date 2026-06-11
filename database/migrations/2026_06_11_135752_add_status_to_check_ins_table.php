<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_ins', function (Blueprint $table): void {
            $table->string('status', 20)->default('success')->after('checked_out_at');
            $table->string('denied_reason', 40)->nullable()->after('status');

            $table->index(['member_id', 'status', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table): void {
            $table->dropIndex(['member_id', 'status', 'checked_in_at']);
            $table->dropColumn(['status', 'denied_reason']);
        });
    }
};
