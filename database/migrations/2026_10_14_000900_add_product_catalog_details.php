<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type', 24)->default('material')->index();
            $table->string('manufacturing_company', 160)->nullable();
            $table->string('manufacturing_country', 120)->nullable();
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('storage_info')->nullable();
            $table->text('risk_info')->nullable();
            $table->text('usage_info')->nullable();
            $table->string('driver_name', 120)->nullable();
            $table->string('driver_phone', 32)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_type']);
            $table->dropColumn([
                'product_type',
                'manufacturing_company',
                'manufacturing_country',
                'production_date',
                'expiry_date',
                'storage_info',
                'risk_info',
                'usage_info',
                'driver_name',
                'driver_phone',
            ]);
        });
    }
};
