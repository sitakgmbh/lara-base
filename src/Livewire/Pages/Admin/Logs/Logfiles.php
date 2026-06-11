<?php

namespace Sitakgmbh\LaraBase\Livewire\Pages\Admin\Logs;

use Illuminate\Support\Facades\File;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Sitakgmbh\LaraBase\Facades\LaraLog;
// use Sitakgmbh\LaraBase\Facades\LaraToast;

#[Layout('lara-base::layouts.app')]
class Logfiles extends Component
{
    public array   $files = [];
    public ?string $status = null;
    public array   $offsets = [];
    public int     $linesPerPage = 500;

    private const FILE_ORDER = [
        'server.log',
        'debug.log',
        'laravel.log',
    ];

    public function mount(): void
    {
        $this->loadFiles();
    }

    #[On('logfile-deleted')]
    public function refreshFiles(string $filename): void
    {
        $this->loadFiles();
        $this->status = "Datei {$filename} wurde erfolgreich gelöscht.";
    }

    private function loadFiles(): void
    {
        $logPath = storage_path('logs');

        if (!File::exists($logPath)) {
            $this->files = [];
            return;
        }

        $this->files = collect(File::files($logPath))
            ->map(function ($file) {
                try {
                    $totalLines = $this->countLines($file->getPathname());
                    $truncated  = $totalLines > $this->linesPerPage;
                    $content    = $this->tailFile($file->getPathname(), $this->linesPerPage);

                    return [
                        'name'      => $file->getFilename(),
                        'size'      => $this->formatSize($file->getSize()),
                        'updated'   => date('Y-m-d H:i:s', $file->getMTime()),
                        'content'   => $content,
                        'truncated' => $truncated,
                    ];
                } catch (\Exception) {
                    return null;
                }
            })
            ->filter()
            ->sortBy(function ($file) {
                $index = array_search($file['name'], self::FILE_ORDER);

                return $index !== false
                    ? $index
                    : count(self::FILE_ORDER) . $file['name'];
            })
            ->values()
            ->toArray();
    }

    private function countLines(string $path): int
    {
        $count  = 0;
        $handle = fopen($path, 'r');

        while (!feof($handle)) {
            fgets($handle);
            $count++;
        }

        fclose($handle);

        return $count;
    }

    private function tailFile(string $path, int $lines): string
    {
        $file = new \SplFileObject($path);

        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $start  = max(0, $totalLines - $lines);
        $result = [];

        $file->seek($start);

        while (!$file->eof()) {
            $result[] = $file->current();
            $file->next();
        }

        return implode('', $result);
    }

    public function loadMore(string $filename): void
    {
        $path = storage_path('logs/' . basename($filename));

        if (!File::exists($path)) {
            return;
        }

        $totalLines    = $this->countLines($path);
        $currentOffset = $this->offsets[$filename] ?? $this->linesPerPage;
        $newOffset     = min($currentOffset + $this->linesPerPage, $totalLines);

        $windowSize = 2000;
        $end        = max(0, $totalLines - $newOffset);
        $start      = max(0, $end - $windowSize);

        $file = new \SplFileObject($path);

        $result = [];
        $file->seek($start);

        $lineCount = 0;

        while (!$file->eof() && $lineCount < $windowSize) {
            $result[] = $file->current();
            $file->next();
            $lineCount++;
        }

        $content = implode('', $result);

        $this->offsets[$filename] = $newOffset;

        foreach ($this->files as &$f) {
            if ($f['name'] === $filename) {
                $f['content']   = $content;
                $f['truncated'] = $newOffset < $totalLines;
                break;
            }
        }
    }

    public function download(string $filename)
    {
        $path = storage_path('logs/' . basename($filename));

        if (!File::exists($path)) {
            $this->dispatch('open-modal', modal: 'components.modals.alert-modal', payload: [
                'message'  => "Das Logfile {$filename} konnte nicht gefunden werden.",
                'headline' => 'Fehler',
                'color'    => 'bg-danger',
                'icon'     => 'ri-close-circle-line',
            ]);

            return;
        }

        $user     = auth()->user();
        $username = $user?->username ?? 'unbekannt';

        LaraLog::db('system', 'info', "Logfile {$filename} heruntergeladen durch {$username}", [
            'username' => $username,
            'file'     => $filename,
        ]);

        return response()->download($path);
    }

    public function delete(string $filename): void
    {
        $path = storage_path('logs/' . basename($filename));

        if (!File::exists($path)) {
            $this->dispatch('open-modal', modal: 'components.modals.alert-modal', payload: [
                'message'  => "Das Logfile {$filename} konnte nicht gefunden werden.",
                'headline' => 'Fehler',
                'color'    => 'bg-danger',
                'icon'     => 'ri-close-circle-line',
            ]);

            return;
        }

        $user     = auth()->user();
        $username = $user?->username ?? 'unbekannt';

        LaraLog::db('system', 'info', "Logfile {$filename} gelöscht durch {$username}", [
            'username' => $username,
            'file'     => $filename,
        ]);

        File::delete($path);

        $this->dispatch('logfile-deleted', filename: $filename);
    }

    public function confirmDelete(string $filename): void
    {
        $this->dispatch(
            'open-modal',
            modal: 'components.modals.confirm-modal',
            payload: [
                'title'          => 'Logfile löschen',
                'message'        => "Soll <strong>{$filename}</strong> wirklich gelöscht werden?",
                'hint'           => 'Dieser Vorgang kann nicht rückgängig gemacht werden.',
                'confirmLabel'   => 'Löschen',
                'confirmClass'   => 'btn-danger',
                'headerBg'       => 'bg-danger',
                'confirmEvent'   => 'logfile-delete-confirmed',
                'confirmPayload' => ['filename' => $filename],
            ]
        );
    }

    #[On('logfile-delete-confirmed')]
    public function deleteConfirmed(string $filename): void
    {
        $this->delete($filename);
		$this->dispatch('toast', message: 'Logfile erfolgreich gelöscht.', type: 'success');
    }

    public function render()
    {
        return view('lara-base::livewire.pages.admin.logs.logfiles')
            ->layoutData(['pageTitle' => 'Logfiles']);
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units  = ['KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen((string) $bytes) - 1) / 3);

        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor - 1]);
    }
}