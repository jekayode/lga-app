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
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('role')->default('community_member')->after('phone')->index();
            $table->string('referral_code')->nullable()->unique()->after('role');
            $table->foreignId('referred_by_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->foreignId('profession_id')->nullable()->after('referred_by_id')->constrained()->nullOnDelete();
            $table->boolean('has_disability')->default(false)->after('profession_id');
            $table->string('disability_notes')->nullable()->after('has_disability');
            $table->foreignId('local_government_id')->nullable()->after('disability_notes')->constrained()->nullOnDelete();
            $table->foreignId('ward_id')->nullable()->after('local_government_id')->constrained()->nullOnDelete();
            $table->foreignId('polling_unit_id')->nullable()->after('ward_id')->constrained()->nullOnDelete();
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
            $table->timestamp('otp_verified_at')->nullable()->after('phone_verified_at');
            $table->boolean('must_setup_two_factor')->default(false)->after('otp_verified_at');
            $table->boolean('is_active')->default(true)->after('must_setup_two_factor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_id');
            $table->dropConstrainedForeignId('profession_id');
            $table->dropConstrainedForeignId('local_government_id');
            $table->dropConstrainedForeignId('ward_id');
            $table->dropConstrainedForeignId('polling_unit_id');
            $table->dropColumn([
                'phone',
                'role',
                'referral_code',
                'has_disability',
                'disability_notes',
                'phone_verified_at',
                'otp_verified_at',
                'must_setup_two_factor',
                'is_active',
            ]);
        });
    }
};
