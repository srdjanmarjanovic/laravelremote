<?php

use App\Enums\ListingType;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
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
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('tier')->default(ListingType::Regular->value);
            $table->string('type')->default(PaymentType::Initial->value);
            $table->string('provider')->default(PaymentProvider::LemonSqueezy->value);
            $table->string('provider_payment_id')->nullable();
            $table->string('status')->default(PaymentStatus::Pending->value);
            $table->timestamps();

            $table->index(['position_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('provider_payment_id');
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
