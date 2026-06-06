<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('mpesa-invoices.table', 'invoice_records'), function (Blueprint $table) {
            $table->id();

            $table->string('invoice_number', 30)->unique()->index();
            $table->string('mpesa_receipt', 30)->nullable()->index();
            $table->decimal('amount', 12, 2)->default(0);

            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_name', 180)->nullable();
            $table->string('customer_email', 180)->nullable();

            $table->string('pdf_path')->nullable();
            $table->timestamp('emailed_at')->nullable();

            // Full invoice snapshot for reconstruction
            $table->json('payload')->nullable();

            // Polymorphic link to the owning model (e.g. Order, Subscription)
            $table->nullableMorphs('invoiceable');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('mpesa-invoices.table', 'invoice_records'));
    }
};
