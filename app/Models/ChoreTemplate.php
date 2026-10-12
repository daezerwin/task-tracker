<?php

namespace App\Models;

use App\Enums\RepeatFrequency;
use App\Enums\TaskCategory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A starter chore offered to every user (seeded by ChoreTemplateSeeder) and
 * copied into their own task list by App\Services\StarterChores — users never
 * edit templates directly, only their copies.
 */
#[Fillable([
    'title', 'category', 'notes', 'repeat', 'repeat_interval',
    'repeat_weekdays', 'month_day', 'due_time', 'sort_order',
])]
class ChoreTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'category' => TaskCategory::class,
            'repeat' => RepeatFrequency::class,
            'repeat_interval' => 'integer',
            'repeat_weekdays' => 'array',
            'month_day' => 'integer',
        ];
    }
}
