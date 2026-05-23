<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Sitakgmbh\LaraBase\Models\Setting;

#[Layout('lara-base::layouts.app')]
class SettingsPage extends Component
{
    public array $settings = [];

    public function mount(): void
    {
        $this->settings = Setting::all()
            ->map(function ($s) {
                return [
                    'key'         => $s->key,
                    'value'       => $s->type === 'password' ? ($s->value ?? '') : $s->value,
                    'type'        => $s->type,
                    'name'        => $s->name,
                    'description' => $s->description,
                    'group'       => $s->group ?? 'System',
                ];
            })
            ->groupBy('group')
            ->toArray();
    }

    public function save(): void
    {
        foreach ($this->settings as $group => $items) {
            foreach ($items as $s) {
                \Cache::forget("setting_{$s['key']}");

                Setting::updateOrCreate(
                    ['key' => $s['key']],
                    ['value' => $s['value'], 'type' => $s['type']]
                );
            }
        }

		\Sitakgmbh\LaraBase\Support\LaraToast::success('Einstellungen gespeichert', '', $this);
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.settings')
            ->layoutData(['pageTitle' => 'Einstellungen']);
    }
}