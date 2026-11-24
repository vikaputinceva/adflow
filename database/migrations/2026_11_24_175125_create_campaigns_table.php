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
    Schema::create('campaigns', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('platform_name');
        $table->string('status')->default('active');
        $table->decimal('budget_total', 12, 2);
        $table->decimal('budget_spent', 12, 2)->default(0);
        $table->decimal('revenue_attributed', 12, 2)->default(0);
        $table->date('end_date');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
