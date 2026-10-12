<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            // Plain strings validated against App\Enums rather than DB enums,
            // so adding a category or frequency never needs a table rebuild.
            $table->string('category', 32)->default('other');
            $table->text('notes')->nullable();
            $table->date('starts_on');
            $table->string('due_time', 5)->nullable();
            $table->string('repeat', 16)->default('none');
            $table->unsignedSmallInteger('repeat_interval')->default(1);
            $table->json('repeat_weekdays')->nullable();
            $table->date('ends_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
