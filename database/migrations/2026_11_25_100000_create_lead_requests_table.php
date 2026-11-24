<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_requests', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('contact');
            $table->string('platform_name');
            $table->decimal('budget_estimate', 12, 2)->default(0);
            $table->string('status')->default('new'); // new, in_work, done
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_requests');
    }
};
