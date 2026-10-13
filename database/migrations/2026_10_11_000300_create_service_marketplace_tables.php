<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('location');
            $table->decimal('budget_min', 10, 2)->nullable();
            $table->decimal('budget_max', 10, 2)->nullable();
            $table->string('status', 24)->default('open')->index();
            $table->foreignId('accepted_provider_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['category_id', 'status']);
            $table->index(['customer_id', 'created_at']);
        });

        Schema::create('service_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->restrictOnDelete();
            $table->text('message');
            $table->decimal('amount', 10, 2);
            $table->unsignedInteger('estimated_days')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->timestamps();
            $table->unique(['service_request_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_quotes');
        Schema::dropIfExists('service_requests');
    }
};
