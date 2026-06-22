<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'staff_profiles',
            'shifts',
            'shift_assignments',
            'attendances',
            'leaves',
            'payroll_periods',
            'payroll_items',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'payroll_items',
            'payroll_periods',
            'leaves',
            'attendances',
            'shift_assignments',
            'shifts',
            'staff_profiles',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
