<?php

namespace Database\Factories;

use App\Enums\TaskCategory;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => ucfirst(fake()->words(3, true)),
            'category' => fake()->randomElement(TaskCategory::cases()),
            'starts_on' => today(),
            'repeat' => 'none',
            'repeat_interval' => 1,
            'is_active' => true,
        ];
    }

    public function daily(int $interval = 1): static
    {
        return $this->state(['repeat' => 'daily', 'repeat_interval' => $interval]);
    }

    /** @param list<int> $weekdays */
    public function weekly(array $weekdays, int $interval = 1): static
    {
        return $this->state(['repeat' => 'weekly', 'repeat_weekdays' => $weekdays, 'repeat_interval' => $interval]);
    }

    public function monthly(int $interval = 1): static
    {
        return $this->state(['repeat' => 'monthly', 'repeat_interval' => $interval]);
    }
}
