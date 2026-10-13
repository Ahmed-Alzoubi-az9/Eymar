<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 24)->default('customer')->index();
            $table->string('phone', 32)->nullable();
            $table->string('location', 160)->nullable();
            $table->string('business_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('status', 24)->default('active')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn(['role', 'phone', 'location', 'business_name', 'bio', 'status']);
        });
    }
};
