<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // subscriptions — filtered heavily by date range and status in AnalyticsService and widgets
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('start_date');
            $table->index('end_date');
            $table->index('status');
            $table->index('deleted_at');
        });

        // invoice_transactions — filtered by occurred_at + type in every financial query
        Schema::table('invoice_transactions', function (Blueprint $table) {
            $table->index('occurred_at');
            $table->index('type');
        });

        // invoices — filtered by date, status, due_amount in overdue/outstanding queries
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('date');
            $table->index('status');
            $table->index('due_amount');
        });

        // members — soft-delete filter runs on all member queries
        Schema::table('members', function (Blueprint $table) {
            $table->index('deleted_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('invoice_transactions', function (Blueprint $table) {
            $table->dropIndex(['occurred_at']);
            $table->dropIndex(['type']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['due_amount']);
        });

        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['status']);
        });
    }
};
