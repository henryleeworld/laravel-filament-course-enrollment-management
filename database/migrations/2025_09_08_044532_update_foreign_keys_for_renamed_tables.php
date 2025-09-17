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
        Schema::table('course_classes', function (Blueprint $table) {
            $table->renameColumn('learning_class_id', 'course_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->renameColumn('class_schedule_id', 'course_class_id');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->renameColumn('learning_class_id', 'course_id');
        });

        Schema::table('weekly_schedules', function (Blueprint $table) {
            $table->renameColumn('learning_class_id', 'course_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_schedules', function (Blueprint $table) {
            $table->renameColumn('course_id', 'learning_class_id');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->renameColumn('course_id', 'learning_class_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->renameColumn('course_class_id', 'class_schedule_id');
        });

        Schema::table('course_classes', function (Blueprint $table) {
            $table->renameColumn('course_id', 'learning_class_id');
        });
    }
};
