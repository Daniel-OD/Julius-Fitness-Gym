<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('id');
            $table->string('contact')->nullable()->after('email');
            $table->date('dob')->nullable()->after('contact');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('dob');
            $table->text('address')->nullable()->after('gender');
            $table->string('country')->nullable()->after('address');
            $table->string('state')->nullable()->after('country');
            $table->string('city')->nullable()->after('state');
            $table->string('pincode')->nullable()->after('city');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('pincode');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo', 'contact', 'dob', 'gender', 'address', 'country', 'state', 'city', 'pincode', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
