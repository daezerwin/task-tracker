<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chore_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->string('category', 32);
            $table->text('notes')->nullable();
            $table->string('repeat', 16);
            $table->unsignedSmallInteger('repeat_interval')->default(1);
            $table->json('repeat_weekdays')->nullable();
            $table->unsignedTinyInteger('month_day')->nullable();
            $table->string('due_time', 5)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chore_templates');
    }
};
