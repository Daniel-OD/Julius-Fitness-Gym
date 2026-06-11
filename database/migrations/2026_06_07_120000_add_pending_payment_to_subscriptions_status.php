<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->string('status', 30)->default('ongoing')->change();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->enum('status', ['upcoming', 'ongoing', 'expiring', 'expired', 'renewed'])
                ->default('ongoing')
                ->change();
        });
    }
};
