<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reviews & ratings: linked to completed ORDER or completed PROJECT (service_request),
        // names the reviewer and the reviewee (provider or company). Only one review per order/project per reviewer.
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1..5 enforced in validation
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['reviewee_id', 'rating']);
            $table->unique(['order_id', 'reviewer_id'], 'reviews_order_reviewer_unique');
            $table->unique(['service_request_id', 'reviewer_id'], 'reviews_project_reviewer_unique');
        });

        // Hourly availability slots managed by contractors/craftsmen.
        Schema::create('provider_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true)->index();
            $table->timestamps();

            $table->index(['provider_id', 'date']);
        });

        // Bookings: customer requests a specific hourly slot. 24h expiry handled in app logic.
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->string('status', 16)->default('pending')->index(); // pending|accepted|rejected|expired|completed|cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['provider_id', 'date', 'status']);
        });

        // Direct messages between any two users. Polling gives real-time feel without websockets.
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->index(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'read_at']);
        });

        // Portfolio: one item has many images (multi-image decision).
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 160);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('provider_id');
        });

        Schema::create('portfolio_item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_item_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Payments: never store card numbers/passwords. Only gateway token + last4.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 24)->default('cash_on_delivery')->index(); // online|cash_on_delivery
            $table->decimal('amount', 12, 2);
            $table->string('status', 24)->default('pending')->index(); // pending|paid|failed|refunded
            $table->string('gateway_token', 120)->nullable(); // simulated gateway reference
            $table->char('card_last4', 4)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('portfolio_item_images');
        Schema::dropIfExists('portfolio_items');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('provider_schedules');
        Schema::dropIfExists('reviews');
    }
};
