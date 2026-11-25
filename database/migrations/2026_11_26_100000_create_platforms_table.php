<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Базовые площадки плюс те, что уже встречаются в кампаниях и заявках,
        // чтобы старые данные не потерялись
        $names = ['Яндекс.Директ', 'ВКонтакте', 'Telegram'];
        $names = array_merge(
            $names,
            DB::table('campaigns')->pluck('platform_name')->all(),
            DB::table('lead_requests')->pluck('platform_name')->all()
        );

        foreach (array_unique($names) as $name) {
            DB::table('platforms')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};
