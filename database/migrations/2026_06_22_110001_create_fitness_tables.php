<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_instructor_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['member_id', 'instructor_id']);
        });

        Schema::create('exercise_library', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->json('muscle_groups')->nullable();
            $table->string('equipment')->nullable();
            $table->text('instructions')->nullable();
            $table->string('video_url')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('workout_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('difficulty')->default('beginner');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('workout_template_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('workout_templates')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercise_library')->cascadeOnDelete();
            $table->unsignedTinyInteger('sets')->nullable();
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('member_workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('workout_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('member_workout_plans')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_number');
            $table->string('name')->nullable();
            $table->foreignId('template_id')->nullable()->constrained('workout_templates')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('workout_plan_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_day_id')->constrained('workout_plan_days')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercise_library')->cascadeOnDelete();
            $table->unsignedTinyInteger('sets')->nullable();
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_day_id')->nullable()->constrained('workout_plan_days')->nullOnDelete();
            $table->dateTime('logged_at');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('workout_log_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained('workout_logs')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercise_library')->cascadeOnDelete();
            $table->unsignedTinyInteger('set_number');
            $table->unsignedSmallInteger('reps')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->unsignedSmallInteger('rest_seconds')->nullable();
            $table->timestamps();
        });

        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->decimal('calories_per_100g', 8, 2)->default(0);
            $table->decimal('protein', 8, 2)->default(0);
            $table->decimal('carbs', 8, 2)->default(0);
            $table->decimal('fat', 8, 2)->default(0);
            $table->decimal('fiber', 8, 2)->default(0);
            $table->decimal('serving_size', 8, 2)->default(100);
            $table->string('serving_unit')->default('g');
            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('nutrition_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('daily_calories')->nullable();
            $table->decimal('protein_g', 8, 1)->nullable();
            $table->decimal('carbs_g', 8, 1)->nullable();
            $table->decimal('fat_g', 8, 1)->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('nutrition_plan_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('nutrition_plans')->cascadeOnDelete();
            $table->string('meal_type');
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('nutrition_plan_meal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_id')->constrained('nutrition_plan_meals')->cascadeOnDelete();
            $table->foreignId('food_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->string('unit')->default('g');
            $table->timestamps();
        });

        Schema::create('food_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->date('logged_at');
            $table->string('meal_type');
            $table->foreignId('food_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->string('unit')->default('g');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_logs');
        Schema::dropIfExists('nutrition_plan_meal_items');
        Schema::dropIfExists('nutrition_plan_meals');
        Schema::dropIfExists('nutrition_plans');
        Schema::dropIfExists('food_items');
        Schema::dropIfExists('workout_log_sets');
        Schema::dropIfExists('workout_logs');
        Schema::dropIfExists('workout_plan_exercises');
        Schema::dropIfExists('workout_plan_days');
        Schema::dropIfExists('member_workout_plans');
        Schema::dropIfExists('workout_template_exercises');
        Schema::dropIfExists('workout_templates');
        Schema::dropIfExists('exercise_library');
        Schema::dropIfExists('member_instructor_assignments');
    }
};
