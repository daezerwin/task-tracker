<?php

namespace Database\Seeders;

use App\Models\ChoreTemplate;
use Illuminate\Database\Seeder;

class ChoreTemplateSeeder extends Seeder
{
    // Weekday numbers follow Carbon: 0 = Sunday … 6 = Saturday.
    private const SUN = 0;

    private const MON = 1;

    private const TUE = 2;

    private const WED = 3;

    private const THU = 4;

    private const FRI = 5;

    private const SAT = 6;

    /**
     * The starter chore list users can copy into their own tasks.
     *
     * Synced by title with updateOrCreate on every boot (see
     * docker/entrypoint.sh), and templates no longer listed here are removed —
     * so edits to this list reach existing deployments, unlike an
     * "already seeded? skip" guard would allow.
     */
    public function run(): void
    {
        $chores = [
            // Daily
            ['title' => 'Make the beds', 'category' => 'cleaning', 'repeat' => 'daily', 'due_time' => '07:00'],
            ['title' => 'Water the plants', 'category' => 'outdoor', 'repeat' => 'daily', 'due_time' => '07:30'],
            ['title' => 'Clean floors', 'category' => 'cleaning', 'repeat' => 'daily', 'due_time' => '08:00',
                'notes' => 'Sweep every day; mop as well on Tuesdays and Fridays.'],
            ['title' => 'Wash the dishes', 'category' => 'kitchen', 'repeat' => 'daily', 'due_time' => '20:00'],
            ['title' => 'Wipe kitchen counters & stove', 'category' => 'kitchen', 'repeat' => 'daily', 'due_time' => '20:30'],
            ['title' => 'Take out the kitchen trash', 'category' => 'kitchen', 'repeat' => 'daily', 'due_time' => '21:00'],
            ['title' => 'Tidy up the living room', 'category' => 'cleaning', 'repeat' => 'daily', 'due_time' => '21:30'],

            // Weekly — laundry
            ['title' => 'Do laundry', 'category' => 'laundry', 'repeat' => 'weekly', 'repeat_weekdays' => [self::WED, self::SAT], 'due_time' => '08:00',
                'notes' => 'Wash, hang dry, then fold and put away. Separate whites, colors and darks; check pockets first.'],
            ['title' => 'Change bed sheets & pillowcases', 'category' => 'laundry', 'repeat' => 'weekly', 'repeat_weekdays' => [self::SAT], 'due_time' => '10:00'],
            ['title' => 'Wash towels & bath mats', 'category' => 'laundry', 'repeat' => 'weekly', 'repeat_weekdays' => [self::SUN], 'due_time' => '08:00'],
            ['title' => 'Iron clothes for the week', 'category' => 'laundry', 'repeat' => 'weekly', 'repeat_weekdays' => [self::SUN], 'due_time' => '15:00'],

            // Weekly — cleaning
            ['title' => 'Clean rooms', 'category' => 'cleaning', 'repeat' => 'weekly', 'repeat_weekdays' => [self::TUE, self::FRI], 'due_time' => '09:00',
                'notes' => 'Bedrooms and living areas: tidy up, dust surfaces, sweep under furniture.'],
            ['title' => 'Dust furniture, shelves & appliances', 'category' => 'cleaning', 'repeat' => 'weekly', 'repeat_weekdays' => [self::MON], 'due_time' => '09:00'],
            ['title' => 'Vacuum sofa, rugs & carpets', 'category' => 'cleaning', 'repeat' => 'weekly', 'repeat_weekdays' => [self::THU], 'due_time' => '09:00'],
            ['title' => 'Wipe mirrors & glass surfaces', 'category' => 'cleaning', 'repeat' => 'weekly', 'repeat_weekdays' => [self::FRI], 'due_time' => '10:00'],
            ['title' => 'Clean bathroom', 'category' => 'bathroom', 'repeat' => 'weekly', 'repeat_weekdays' => [self::SAT], 'due_time' => '09:00',
                'notes' => 'Toilet, sink, shower/tub, floor. Restock tissue and soap.'],
            ['title' => 'Take out recyclables', 'category' => 'cleaning', 'repeat' => 'weekly', 'repeat_weekdays' => [self::MON], 'due_time' => '07:00'],

            // Weekly — kitchen & errands
            ['title' => 'Clear out old food from the fridge', 'category' => 'kitchen', 'repeat' => 'weekly', 'repeat_weekdays' => [self::SAT], 'due_time' => '17:00'],
            ['title' => "Plan the week's meals", 'category' => 'kitchen', 'repeat' => 'weekly', 'repeat_weekdays' => [self::SAT], 'due_time' => '19:00'],
            ['title' => 'Grocery shopping', 'category' => 'errands', 'repeat' => 'weekly', 'repeat_weekdays' => [self::SUN], 'due_time' => '10:00'],

            // Every two weeks
            ['title' => 'Clean windows & window sills', 'category' => 'cleaning', 'repeat' => 'weekly', 'repeat_interval' => 2, 'repeat_weekdays' => [self::SAT], 'due_time' => '14:00'],
            ['title' => 'Clean the microwave & oven', 'category' => 'kitchen', 'repeat' => 'weekly', 'repeat_interval' => 2, 'repeat_weekdays' => [self::THU], 'due_time' => '19:00'],
            ['title' => 'Sweep the yard & trim plants', 'category' => 'outdoor', 'repeat' => 'weekly', 'repeat_interval' => 2, 'repeat_weekdays' => [self::SUN], 'due_time' => '07:00'],

            // Monthly
            ['title' => 'Deep clean the refrigerator', 'category' => 'kitchen', 'repeat' => 'monthly', 'month_day' => 1, 'due_time' => '09:00'],
            ['title' => 'Clean aircon filters & electric fans', 'category' => 'maintenance', 'repeat' => 'monthly', 'month_day' => 5, 'due_time' => '10:00'],
            ['title' => 'Pay utility bills', 'category' => 'errands', 'repeat' => 'monthly', 'month_day' => 10, 'due_time' => '09:00',
                'notes' => 'Electricity, water, internet.'],
            ['title' => 'Check LPG tank & stove hose for leaks', 'category' => 'maintenance', 'repeat' => 'monthly', 'month_day' => 15, 'due_time' => '09:00'],
            ['title' => 'Clean the washing machine', 'category' => 'laundry', 'repeat' => 'monthly', 'month_day' => 20, 'due_time' => '10:00'],
            ['title' => 'Restock household supplies', 'category' => 'errands', 'repeat' => 'monthly', 'month_day' => 25, 'due_time' => '10:00',
                'notes' => 'Detergent, dish soap, tissue, trash bags, cleaning sprays.'],
            ['title' => 'Declutter a closet or drawer', 'category' => 'other', 'repeat' => 'monthly', 'month_day' => 28, 'due_time' => '15:00'],

            // Every few months
            ['title' => 'Flip & vacuum the mattress', 'category' => 'cleaning', 'repeat' => 'monthly', 'repeat_interval' => 3, 'month_day' => 1, 'due_time' => '10:00'],
            ['title' => 'Wash curtains', 'category' => 'laundry', 'repeat' => 'monthly', 'repeat_interval' => 3, 'month_day' => 15, 'due_time' => '08:00'],
        ];

        foreach ($chores as $order => $chore) {
            ChoreTemplate::updateOrCreate(['title' => $chore['title']], [
                'category' => $chore['category'],
                'notes' => $chore['notes'] ?? null,
                'repeat' => $chore['repeat'],
                'repeat_interval' => $chore['repeat_interval'] ?? 1,
                'repeat_weekdays' => $chore['repeat_weekdays'] ?? null,
                'month_day' => $chore['month_day'] ?? null,
                'due_time' => $chore['due_time'] ?? null,
                'sort_order' => $order,
            ]);
        }

        ChoreTemplate::query()->whereNotIn('title', array_column($chores, 'title'))->delete();
    }
}
