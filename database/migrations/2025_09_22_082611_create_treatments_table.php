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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->string('visit_id'); // Reference to the visit
            $table->string('patient_id'); // Reference to the patient
            $table->string('doctor_id'); // Reference to the doctor
            $table->string('treatment_name'); // Name of the treatment/procedure
            $table->text('description')->nullable(); // Treatment description
            $table->decimal('cost', 10, 2); // Cost of this treatment
            $table->integer('quantity')->default(1); // Quantity (for multiple sessions)
            $table->decimal('total_cost', 10, 2); // Total cost (cost * quantity)
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
