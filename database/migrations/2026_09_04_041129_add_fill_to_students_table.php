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
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone');
            $table->string('gender')->after('email')->nullable();
            $table->date('dob')->nullable();
            $table->string('origin')->nullable();
            $table->foreignId('department_id')->constrained('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->change();
            $table->dropColumn('phone');
            $table->dropColumn('gender');
            $table->dropColumn('dob');
            $table->dropColumn('origin');
            $table->dropColumn('department_id');
        });
    }
};
