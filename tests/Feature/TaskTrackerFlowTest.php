<?php

namespace Tests\Feature;

use App\Models\ChoreTemplate;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\ChoreTemplateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TaskTrackerFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Wednesday 14 Oct 2026, mid-morning Manila time.
        $this->travelTo(Carbon::parse('2026-10-14 09:30'));
    }

    public function test_dashboard_offers_starter_chores_when_there_are_no_tasks(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('set up your chores')
            ->assertSee('Add starter chores');
    }

    public function test_adding_starter_chores_copies_every_template_once(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->from(route('dashboard'))
            ->post(route('tasks.starter'), $this->everyStarterChore())
            ->assertRedirect(route('dashboard'));

        $this->assertSame(ChoreTemplate::count(), $user->tasks()->count());

        // Running it again only restores what's missing.
        $user->tasks()->where('title', 'Wash the dishes')->delete();
        $this->from(route('tasks.index'))
            ->post(route('tasks.starter'), $this->everyStarterChore())
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status', 'Added 1 starter chore.');

        $this->assertSame(ChoreTemplate::count(), $user->tasks()->count());
        $this->assertSame(1, $user->tasks()->where('title', 'Wash the dishes')->count());
    }

    public function test_starter_chore_wizard_only_adds_the_chores_picked(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $user = User::factory()->create();
        $picked = ChoreTemplate::whereIn('title', ['Wash the dishes', 'Pay utility bills'])->pluck('id');

        $this->actingAs($user)
            ->from(route('tasks.index'))
            ->post(route('tasks.starter'), ['templates' => $picked->all()])
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('status', 'Added 2 starter chores.');

        $this->assertEqualsCanonicalizing(['Wash the dishes', 'Pay utility bills'], $user->tasks()->pluck('title')->all());
    }

    public function test_starter_chore_wizard_needs_at_least_one_chore_picked(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('tasks.index'))
            ->post(route('tasks.starter'), ['templates' => []])
            ->assertSessionHasErrors('templates');

        $this->assertSame(0, $user->tasks()->count());
    }

    public function test_starter_chore_wizard_steps_through_only_the_missing_chores(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $user = User::factory()->create();
        $user->tasks()->create(['title' => 'wash the dishes', 'starts_on' => today()]);
        $this->actingAs($user);

        $this->blade('<x-starter-chores-wizard />')
            ->assertSee('Make the beds')
            ->assertSee('7:00 AM')
            ->assertSee('Monthly on the 10th')
            ->assertDontSee('Wash the dishes');

        $this->post(route('tasks.starter'), $this->everyStarterChore());

        $this->blade('<x-starter-chores-wizard />')
            ->assertSee('You already have every starter chore.');
    }

    public function test_starter_monthly_chores_start_on_their_next_day_of_the_month(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('tasks.starter'), $this->everyStarterChore());

        $bills = $user->tasks()->where('title', 'Pay utility bills')->first();     // the 10th — already past
        $lpg = $user->tasks()->where('title', 'Check LPG tank & stove hose for leaks')->first(); // the 15th

        $this->assertSame('2026-11-10', $bills->starts_on->toDateString());
        $this->assertSame('2026-10-15', $lpg->starts_on->toDateString());
    }

    public function test_starter_chores_include_the_core_repeating_chores(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('tasks.starter'), $this->everyStarterChore());

        $schedules = $user->tasks()
            ->whereIn('title', ['Clean floors', 'Clean rooms', 'Clean bathroom', 'Do laundry'])
            ->get()
            ->mapWithKeys(fn (Task $task) => [$task->title => $task->scheduleLabel()]);

        $this->assertEquals([
            'Clean floors' => 'Every day',
            'Clean rooms' => 'Every Tue & Fri',
            'Clean bathroom' => 'Every Sat',
            'Do laundry' => 'Every Wed & Sat',
        ], $schedules->all());

        // The chores they replaced aren't offered alongside them.
        $this->assertSame(0, ChoreTemplate::whereIn('title', [
            'Sweep the floors', 'Mop the floors', 'Clean the bathroom',
            'Laundry: wash & hang dry', 'Fold & put away clean laundry',
        ])->count());
    }

    public function test_chore_template_seeder_is_idempotent_and_restores_edits(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $count = ChoreTemplate::count();

        ChoreTemplate::where('title', 'Wash the dishes')->update(['due_time' => '03:00']);
        ChoreTemplate::create(['title' => 'Retired chore', 'category' => 'other', 'repeat' => 'daily']);

        $this->seed(ChoreTemplateSeeder::class);

        $this->assertSame($count, ChoreTemplate::count());
        $this->assertSame('20:00', ChoreTemplate::where('title', 'Wash the dishes')->value('due_time'));
        $this->assertDatabaseMissing('chore_templates', ['title' => 'Retired chore']);
    }

    public function test_dashboard_lists_todays_chores_and_ticking_toggles_completion(): void
    {
        $user = User::factory()->create();
        $daily = Task::factory()->for($user)->daily()->create(['title' => 'Wash the dishes', 'starts_on' => '2026-10-01']);
        Task::factory()->for($user)->weekly([6])->create(['title' => 'Clean the bathroom', 'starts_on' => '2026-10-01']);

        $this->actingAs($user);

        // The Saturday chore shows in the week agenda, but isn't counted for today.
        $component = Volt::test('dashboard')
            ->assertSee('Wash the dishes')
            ->assertSee('0 of 1 done');

        $component->call('toggle', $daily->id, '2026-10-14')->assertSee('1 of 1 done');
        $this->assertDatabaseHas('task_completions', ['task_id' => $daily->id, 'due_on' => '2026-10-14']);

        $component->call('toggle', $daily->id, '2026-10-14')->assertSee('0 of 1 done');
        $this->assertDatabaseMissing('task_completions', ['task_id' => $daily->id]);
    }

    public function test_cannot_tick_another_users_task_or_a_day_it_does_not_fall_on(): void
    {
        $user = User::factory()->create();
        $mine = Task::factory()->for($user)->weekly([6])->create(['starts_on' => '2026-10-01']);
        $theirs = Task::factory()->daily()->create(['starts_on' => '2026-10-01']);

        $this->actingAs($user);

        Volt::test('dashboard')->call('toggle', $theirs->id, '2026-10-14')->assertStatus(404);
        Volt::test('dashboard')->call('toggle', $mine->id, '2026-10-14')->assertStatus(404);
        Volt::test('dashboard')->call('toggle', $mine->id, 'not-a-date')->assertStatus(404);

        $this->assertDatabaseCount('task_completions', 0);
    }

    public function test_unfinished_one_off_tasks_show_as_overdue_today(): void
    {
        $user = User::factory()->create();
        $missed = Task::factory()->for($user)->create(['title' => 'Call the plumber', 'starts_on' => '2026-10-10']);
        Task::factory()->for($user)->create(['title' => 'Return library books', 'starts_on' => '2026-10-11'])
            ->completions()->create(['due_on' => '2026-10-11', 'completed_at' => now()]);

        $this->actingAs($user);

        $component = Volt::test('dashboard')
            ->assertSee('Overdue')
            ->assertSee('Call the plumber')
            ->assertDontSee('Return library books');

        // Ticking it off for its original date clears it.
        $component->call('toggle', $missed->id, '2026-10-10')->assertDontSee('Call the plumber');
    }

    public function test_dashboard_day_navigation(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->weekly([6])->create(['title' => 'Change bed sheets', 'starts_on' => '2026-10-01']);

        $this->actingAs($user);

        Volt::test('dashboard')
            ->assertSee('0 of 0 done')
            ->call('nextDay')->call('nextDay')->call('nextDay')
            ->assertSee('Saturday, October 17')
            ->assertSee('0 of 1 done')
            ->call('goToday')
            ->assertSet('dayOffset', 0)
            ->call('jumpTo', '2026-10-17')
            ->assertSet('dayOffset', 3);
    }

    public function test_dashboard_agenda_shows_the_next_seven_days(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->weekly([6])->create(['title' => 'Change bed sheets', 'starts_on' => '2026-10-01', 'due_time' => '10:00']);
        Task::factory()->for($user)->create(['title' => 'Renew passport', 'starts_on' => '2026-10-21']); // the 8th day

        $this->actingAs($user);

        Volt::test('dashboard')
            ->assertSee('Next 7 days')
            ->assertSeeInOrder(['Oct 14', 'Tomorrow', 'Oct 15', 'Saturday', 'Oct 17', 'Change bed sheets', '10:00 AM', 'Tuesday', 'Oct 20'])
            ->assertDontSee('Oct 21')
            ->assertDontSee('Renew passport')
            // The agenda stays anchored on today while another day is open.
            ->call('nextDay')->call('nextDay')
            ->assertSeeInOrder(['Today', 'Oct 14', 'Oct 20']);
    }

    public function test_quick_add_creates_a_task_and_only_closes_the_modal_on_success(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->daily()->create(['starts_on' => '2026-10-01']);

        $this->actingAs($user);

        Volt::test('dashboard')
            ->set('quickTitle', '')
            ->call('saveQuickTask')
            ->assertHasErrors(['quickTitle' => 'required'])
            ->assertNotDispatched('close-modal');

        Volt::test('dashboard')
            ->set('quickTitle', 'Wash the car')
            ->set('quickCategory', 'outdoor')
            ->set('quickTime', '16:00')
            ->set('quickRepeat', 'weekly')
            ->call('saveQuickTask')
            ->assertHasNoErrors()
            ->assertDispatched('close-modal', 'quick-task')
            ->assertSee('Wash the car');

        $task = $user->tasks()->where('title', 'Wash the car')->first();
        $this->assertSame('2026-10-14', $task->starts_on->toDateString());
        $this->assertSame([3], $task->repeat_weekdays);
        $this->assertSame('16:00', $task->due_time);
    }

    public function test_user_can_create_a_weekly_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Mop the floors',
            'category' => 'cleaning',
            'starts_on' => '2026-10-14',
            'due_time' => '09:00',
            'repeat' => 'weekly',
            'repeat_interval' => 1,
            'repeat_weekdays' => ['5', '2'],
        ])->assertRedirect(route('tasks.index'));

        $task = $user->tasks()->first();
        $this->assertSame([2, 5], $task->repeat_weekdays);
        $this->assertTrue($task->is_active);
        $this->assertSame('Every Tue & Fri', $task->scheduleLabel());
    }

    public function test_fields_that_do_not_apply_to_the_repeat_are_cleared(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Renew passport',
            'category' => 'errands',
            'starts_on' => '2026-10-20',
            'repeat' => 'none',
            'repeat_interval' => 4,
            'repeat_weekdays' => ['1'],
            'ends_on' => '2026-12-01',
        ])->assertRedirect(route('tasks.index'));

        $task = $user->tasks()->first();
        $this->assertSame(1, $task->repeat_interval);
        $this->assertNull($task->repeat_weekdays);
        $this->assertNull($task->ends_on);
    }

    public function test_weekly_task_without_weekdays_defaults_to_the_start_dates_weekday(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'Iron clothes',
            'category' => 'laundry',
            'starts_on' => '2026-10-18', // a Sunday
            'repeat' => 'weekly',
        ]);

        $this->assertSame([0], $user->tasks()->first()->repeat_weekdays);
    }

    public function test_task_validation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('tasks.store'), [
            'title' => '',
            'category' => 'gardening',
            'starts_on' => '2026-10-14',
            'repeat' => 'yearly',
            'ends_on' => '2026-10-01',
        ])->assertSessionHasErrors(['title', 'category', 'repeat', 'ends_on']);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_user_can_update_pause_and_delete_a_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->daily()->create(['starts_on' => '2026-10-01']);
        $task->completions()->create(['due_on' => '2026-10-13', 'completed_at' => now()]);

        $this->actingAs($user);

        $this->get(route('tasks.edit', $task))->assertOk();

        $this->put(route('tasks.update', $task), [
            'title' => 'Water the plants',
            'category' => 'outdoor',
            'starts_on' => '2026-10-01',
            'repeat' => 'daily',
            'repeat_interval' => 2,
            'is_active' => '0',
        ])->assertRedirect(route('tasks.index'));

        $task->refresh();
        $this->assertSame('Water the plants', $task->title);
        $this->assertSame(2, $task->repeat_interval);
        $this->assertFalse($task->is_active);

        $this->delete(route('tasks.destroy', $task))->assertRedirect(route('tasks.index'));
        $this->assertModelMissing($task);
        $this->assertDatabaseCount('task_completions', 0);
    }

    public function test_user_cannot_edit_update_or_delete_another_users_task(): void
    {
        $task = Task::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->get(route('tasks.edit', $task))->assertForbidden();
        $this->put(route('tasks.update', $task), ['title' => 'Hijacked'])->assertForbidden();
        $this->delete(route('tasks.destroy', $task))->assertForbidden();
        $this->assertModelExists($task);
    }

    public function test_tasks_index_groups_by_category_with_schedules(): void
    {
        $user = User::factory()->create();
        Task::factory()->for($user)->weekly([3, 6])->create(['title' => 'Laundry', 'category' => 'laundry', 'starts_on' => '2026-10-01']);
        Task::factory()->for($user)->daily()->create(['title' => 'Sweep the floors', 'category' => 'cleaning', 'starts_on' => '2026-10-01', 'due_time' => '08:00']);
        Task::factory()->for($user)->daily()->create(['title' => 'Old chore', 'category' => 'cleaning', 'starts_on' => '2026-10-01', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSeeInOrder(['Cleaning', 'Sweep the floors', 'Every day · 8:00 AM', 'Laundry', 'Every Wed &amp; Sat'], false)
            ->assertSee('Old chore · Paused')
            ->assertSee('Next: today');
    }

    public function test_create_form_prefills_a_date_from_the_calendar(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('tasks.create', ['date' => '2026-10-20']))->assertOk()->assertSee('value="2026-10-20"', false);
        $this->get(route('tasks.create', ['date' => 'garbage']))->assertOk()->assertSee('value="2026-10-14"', false);
    }

    public function test_calendar_shows_the_month_and_lets_you_tick_a_selected_day(): void
    {
        $user = User::factory()->create();
        $bathroom = Task::factory()->for($user)->weekly([6])->create(['title' => 'Clean the bathroom', 'starts_on' => '2026-10-01']);
        $bills = Task::factory()->for($user)->monthly()->create(['title' => 'Pay utility bills', 'starts_on' => '2026-10-10']);

        $this->actingAs($user);
        $this->get(route('calendar'))->assertOk()->assertSee('October 2026');

        $component = Volt::test('calendar')
            ->assertSee('October 2026')
            ->assertSee('Clean the bathroom')
            ->call('selectDay', '2026-10-17')
            ->assertSet('selectedDate', '2026-10-17')
            ->call('toggle', $bathroom->id, '2026-10-17');

        $this->assertDatabaseHas('task_completions', ['task_id' => $bathroom->id, 'due_on' => '2026-10-17']);

        $component->call('nextMonth')
            ->assertSee('November 2026')
            ->assertSet('selectedDate', '2026-11-01')
            ->call('selectDay', '2026-11-10')
            ->assertSee('Pay utility bills')
            ->call('goToday')
            ->assertSet('month', '2026-10')
            ->assertSet('selectedDate', '2026-10-14');

        // Locked state can't be pushed from the browser.
        $this->expectException(CannotUpdateLockedPropertyException::class);
        Volt::test('calendar')->set('month', 'garbage');
    }

    public function test_every_page_renders(): void
    {
        $this->seed(ChoreTemplateSeeder::class);
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('tasks.starter'), $this->everyStarterChore());
        $task = $user->tasks()->first();

        foreach ([route('dashboard'), route('calendar'), route('tasks.index'), route('tasks.create'), route('tasks.edit', $task), route('profile')] as $url) {
            $this->get($url)->assertOk();
        }
    }

    /** What the starter chore wizard submits when every chore is added. */
    private function everyStarterChore(): array
    {
        return ['templates' => ChoreTemplate::pluck('id')->all()];
    }
}
