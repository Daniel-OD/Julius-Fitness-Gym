<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_expiration_notification_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('subscription_id');
            $table->timestamp('read_at');
            $table->timestamps();

            $table->foreign('user_id', 'sub_exp_notif_reads_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('subscription_id', 'sub_exp_notif_reads_sub_fk')
                ->references('id')
                ->on('subscriptions')
                ->cascadeOnDelete();

            $table->unique(['user_id', 'subscription_id'], 'sub_exp_notif_reads_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_expiration_notification_reads');
    }
};
