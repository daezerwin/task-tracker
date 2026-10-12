<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\StarterChores;
use App\Services\TaskSchedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Safe to run repeatedly — the Docker
     * entrypoint runs it on every boot.
     */
    public function run(): void
    {
        $this->call([
            ChoreTemplateSeeder::class,
        ]);

        if (app()->environment('local')) {
            $this->seedLocalDemoUser();
        }
    }

    /**
     * A test@example.com / password account with the starter chores and the
     * past week partly ticked off, so the dashboard and calendar have
     * something to show in development.
     */
    private function seedLocalDemoUser(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password', 'email_verified_at' => now()],
        );

        StarterChores::addTo($user, today()->subDays(7));

        $pastWeek = (new TaskSchedule($user))->between(today()->subDays(7), today()->subDay());

        foreach ($pastWeek as $date => $items) {
            foreach ($items as $index => $item) {
                if ($index % 4 !== 3) {
                    $item['task']->completions()->firstOrCreate(['due_on' => $date], ['completed_at' => $date.' 12:00:00']);
                }
            }
        }
    }
}
