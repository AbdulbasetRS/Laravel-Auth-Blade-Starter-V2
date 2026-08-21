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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // الأمان
            $table->boolean('enable_two_factor')->default(false);
            $table->string('google2fa_secret')->nullable();

            // التوقيت
            $table->string('timezone', 100)->default('Africa/Cairo');
            $table->enum('date_format', [
                'Y-m-d',     // 2025-10-26
                'd-m-Y',     // 26-10-2025
                'm/d/Y',     // 10/26/2025
                'd/m/Y',     // 26/10/2025
                'M d, Y',    // Oct 26, 2025
            ])->default('Y-m-d');
            $table->enum('time_format', [
                '24h',   // 23:59
                '12h',   // 11:59 PM
            ])->default('24h');

            // الإشعارات
            $table->boolean('notifications_email')->default(false);
            $table->boolean('notifications_sound')->default(false);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};