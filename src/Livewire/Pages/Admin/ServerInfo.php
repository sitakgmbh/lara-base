<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class ServerInfo extends Component
{
    public array $infos = [];

    public function mount(): void
    {
        $this->infos = [
            [
                'name'    => 'System',
                'data'    => [
                    'Betriebssystem'  => php_uname(),
                    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unbekannt',
                    'PHP Version'     => PHP_VERSION,
                    'Environment'     => app()->environment(),
                    'Timezone'        => config('app.timezone'),
                ],
            ],
            [
                'name'    => 'Limits',
                'data'    => [
                    'Memory Limit'        => ini_get('memory_limit'),
                    'Max Execution Time'  => ini_get('max_execution_time') . ' Sekunden',
                    'Upload Max Filesize' => ini_get('upload_max_filesize'),
                    'Post Max Size'       => ini_get('post_max_size'),
                ],
            ],
            [
                'name'    => 'Extensions',
                'data'    => [
                    'Loaded Extensions' => implode(', ', get_loaded_extensions()),
                ],
            ],
        ];
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.server-info')
            ->layoutData(['pageTitle' => 'Server Informationen']);
    }
}