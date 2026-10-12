<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['due_on', 'completed_at'])]
class TaskCompletion extends Model
{
    // due_on is deliberately not cast to a date: Eloquent would write it back
    // as "Y-m-d 00:00:00", which no longer matches a plain "Y-m-d" in where
    // clauses. Always read and write it as a Y-m-d string.
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
