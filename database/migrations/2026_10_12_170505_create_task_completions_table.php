<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            // The occurrence this ticks off, stored as a plain Y-m-d string so
            // it compares exactly in queries (see App\Models\TaskCompletion).
            $table->date('due_on');
            $table->dateTime('completed_at');
            $table->timestamps();

            $table->unique(['task_id', 'due_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};
