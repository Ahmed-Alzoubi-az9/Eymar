<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 32)->nullable()->unique();
            $table->unsignedTinyInteger('age')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_image_path')->nullable();
            $table->string('company_owner', 120)->nullable();
            $table->string('company_logo_path')->nullable();
            $table->date('established_date')->nullable();
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                DB::table('users')->where('id', $user->id)->update([
                    'username' => 'user_'.$user->id,
                ]);
            }
        });

        Schema::create('service_provider_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider_type', 24)->index();
            $table->unsignedTinyInteger('experience_years')->nullable();
            $table->text('previous_work')->nullable();
            $table->string('pricing_basis', 16)->nullable();
            $table->decimal('service_rate', 10, 2)->nullable();
            $table->boolean('is_available')->default(true)->index();
            $table->timestamps();
        });

        DB::table('users')
            ->whereIn('role', ['contractor', 'craftsman'])
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('service_provider_profiles')->insert([
                        'user_id' => $user->id,
                        'provider_type' => $user->role,
                        'is_available' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        DB::table('users')->whereIn('role', ['contractor', 'craftsman'])->update([
            'role' => 'customer',
        ]);
    }

    public function down(): void
    {
        foreach (DB::table('service_provider_profiles')->get(['user_id', 'provider_type']) as $profile) {
            DB::table('users')->where('id', $profile->user_id)->update([
                'role' => $profile->provider_type,
            ]);
        }

        Schema::dropIfExists('service_provider_profiles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn([
                'username',
                'age',
                'address',
                'profile_image_path',
                'company_owner',
                'company_logo_path',
                'established_date',
            ]);
        });
    }
};
