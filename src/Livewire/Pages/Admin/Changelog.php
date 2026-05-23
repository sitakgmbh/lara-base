<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('lara-base::layouts.app')]
class Changelog extends Component
{
    public array $entries = [];

    public function mount(): void
    {
        $file = base_path('CHANGELOG.md');

        if (File::exists($file)) {
            $markdown = File::get($file);

            preg_match_all(
                '/^##\s+\[(.*?)\]\s+–\s+(.*?)$(.*?)(?=^##\s+\[|\z)/ms',
                $markdown,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $entry) {
                $this->entries[] = [
                    'version' => trim($entry[1]),
                    'date'    => trim($entry[2]),
                    'body'    => $this->simpleMarkdown(trim($entry[3])),
                ];
            }
        }
    }

    private function simpleMarkdown(string $text): string
    {
        $text = preg_replace('/^#### (.*)$/m', '<h4>$1</h4>', $text);
        $text = preg_replace('/^### (.*)$/m',  '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*)$/m',   '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*)$/m',    '<h1>$1</h1>', $text);
        $text = preg_replace('/\*\*\*(.*?)\*\*\*/s', '<strong><em>$1</em></strong>', $text);
        $text = preg_replace('/\*\*(.*?)\*\*/s',     '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.*?)\*/s',          '<em>$1</em>', $text);
        $text = preg_replace('/^- (.*)$/m',    '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>)/sU', '<ul>$1</ul>', $text);
        $text = preg_replace("/\n{2,}/", '</p><p>', $text);
        return '<p>' . $text . '</p>';
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.changelog')
            ->layoutData(['pageTitle' => 'Changelog']);
    }
}