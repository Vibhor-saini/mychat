<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {

        // Status Message (e.g., "Progress over perfection")
        $table->string('status_message')->nullable()->after('profile_image');
        
        // Availability Status (e.g., available, busy, away, offline)
        $table->string('availability')->default('available')->after('status_message');
    });
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['status_message', 'availability']);
    });
}
};
