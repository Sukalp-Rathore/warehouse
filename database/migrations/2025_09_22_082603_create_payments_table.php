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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('visit_id'); // Reference to the visit
            $table->string('patient_id'); // Reference to the patient
            $table->string('doctor_id'); // Reference to the doctor
            $table->decimal('total_amount', 10, 2); // Total treatment cost
            $table->decimal('paid_amount', 10, 2); // Amount paid in this visit
            $table->decimal('pending_amount', 10, 2); // Remaining amount
            $table->decimal('total_paid_so_far', 10, 2)->default(0); // Total paid across all visits
            $table->string('payment_method')->nullable(); // Cash, Card, UPI, etc.
            $table->text('notes')->nullable(); // Additional notes
            $table->string('status')->default('partial'); // partial, completed, pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
