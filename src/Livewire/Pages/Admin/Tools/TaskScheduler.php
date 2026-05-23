<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Tools;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('lara-base::layouts.app')]
class TaskScheduler extends Component
{
    public array  $tasks       = [];
    public array  $openDetails = [];
    public bool   $running     = false;
    public string $sortBy      = 'task';
    public string $sortDir     = 'asc';

    public function mount(): void
    {
        $this->loadTasks();
    }

    public function toggleDetails(string $key): void
    {
        $this->openDetails[$key] = !($this->openDetails[$key] ?? false);
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy  = $field;
            $this->sortDir = 'asc';
        }
    }

    public function run(string $command): void
    {
        if ($this->running) return;

        $this->running = true;

        try {
            $start     = microtime(true);
            $startedAt = now();

            Artisan::call($command);
            $output = Artisan::output();

            $duration = round(microtime(true) - $start, 2) . ' Sekunden';

            $this->dispatch('open-modal',
                modal: 'components.modals.artisan-output',
                payload: [
                    'command'  => $command,
                    'output'   => $output,
                    'started'  => $startedAt->format('d.m.Y H:i:s'),
                    'duration' => $duration,
                ]
            );
		} catch (\Throwable $e) {
			$this->dispatch('open-modal',
				modal: 'components.modals.artisan-output',
				payload: [
					'command'     => $command,
					'output'      => 'Fehler: ' . $e->getMessage(),
					'started'     => now()->format('d.m.Y H:i:s'),
					'duration'    => '—',
					'headerBg'    => 'bg-danger',
					'headerText'  => 'text-white',
				]
			);
		} finally {
            $this->running = false;
        }
    }

    private function loadTasks(): void
    {
        $allowed  = config('lara-base.task_scheduler.allowed', []);
        $schedule = app(Schedule::class);
        $all      = Artisan::all();

        $scheduled = collect($schedule->events())
            ->filter(function ($event) use ($allowed) {
                $command = $this->extractCommandName($event->command);
                return in_array($command, $allowed, true);
            })
            ->groupBy(fn($event) => $this->extractCommandName($event->command))
            ->map(function ($group, $command) use ($all) {
                $times = $group
                    ->map(fn($e) => $this->extractTimeFromCron($e->expression))
                    ->filter()->unique()->sort()->values();

                $nextRun = $group
                    ->map(fn($e) => $e->nextRunDate(now()))
                    ->filter()->sort()->first();

                return [
                    'key'         => $command,
                    'task'        => $command,
                    'description' => $all[$command]->getDescription() ?? '',
                    'interval'    => $this->humanReadableGroupedInterval($times, $group),
                    'nextRun'     => $nextRun ? Carbon::parse($nextRun)->format('d.m.Y H:i') : '—',
                    'scheduled'   => true,
                ];
            });

			$missing = collect($allowed)
				->diff($scheduled->keys())
				->filter(fn($cmd) => isset($all[$cmd]))
				->map(function ($command) use ($all) {
					return [
						'key'         => $command,
						'task'        => $command,
						'description' => $all[$command]->getDescription() ?? '',
						'interval'    => '—',
						'nextRun'     => '—',
						'scheduled'   => false,
					];
				});

        $this->tasks = $scheduled->values()->merge($missing->values())->toArray();
    }

    private function extractCommandName(string $command): string
    {
        if (preg_match('/[\'"]?artisan[\'"]?\s+([^\s]+)/i', $command, $matches)) {
            return trim($matches[1]);
        }
        $parts = explode(' ', $command);
        return trim(end($parts));
    }

    private function extractTimeFromCron(string $expression): ?string
    {
        $parts = explode(' ', $expression);
        if (count($parts) < 2) return null;

        [$min, $hour] = $parts;

        if (is_numeric($hour) && is_numeric($min)) {
            return sprintf('%02d:%02d', $hour, $min);
        }

        return null;
    }

    private function humanReadableGroupedInterval($times, $events = null): string
    {
        if ($times->count() > 1) return 'Täglich um ' . $times->implode(', ');
        if ($times->count() === 1) return 'Täglich um ' . $times->first();

        if ($events) {
            $expr = $events->first()->expression;
            if ($expr === '0 * * * *')  return 'Stündlich';
            if ($expr === '* * * * *')  return 'Jede Minute';
            if (preg_match('/^\*\/(\d+) \* \* \* \*$/', $expr, $m)) return "Alle {$m[1]} Minuten";
        }

        return '—';
    }

    public function render()
    {
        $rows = collect($this->tasks)->sortBy($this->sortBy, SORT_NATURAL, $this->sortDir === 'desc');

        return view('lara-base::livewire.pages.admin.tools.task-scheduler', [
            'rows' => $rows,
        ])->layoutData(['pageTitle' => 'Aufgabenplanung']);
    }
}