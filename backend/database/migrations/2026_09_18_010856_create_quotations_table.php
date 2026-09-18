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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            // Client data
            $table->string('first_name');
            $table->string('last_name');
            $table->string('identification_number')->index();
            $table->string('email')->index();
            $table->date('birth_date');

            // Trip data
            $table->string('destination_country');
            $table->string('destination_country_code', 10);
            $table->string('destination_region');
            $table->string('destination_flag_url')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('days_count');

            // Financial breakdown
            $table->decimal('base_rate_per_day', 8, 2)->default(3.00);
            $table->decimal('base_amount', 10, 2);
            $table->decimal('surcharge_percentage', 5, 2)->default(0.00);
            $table->decimal('surcharge_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);

            // Status & Lifecycle
            $table->enum('status', ['Cotizado', 'Contratado'])->default('Cotizado')->index();
            $table->timestamp('contracted_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
