<?php

namespace Sitakgmbh\LaraBase\Livewire\Help;

use Illuminate\Support\Facades\File;
use Livewire\Component;

class Viewer extends Component
{
    public string $key      = '';
    public array  $toc      = [];
    public array  $pageRoles = [];
    public array  $pageMeta  = [];
    public string $html     = '';
    public bool   $notFound = false;
    public string $query    = '';
    public array  $results  = [];

    public function mount(string $key): void
    {
        $this->key = $key;
        $this->loadToc();
        $this->resolveKey();
        $this->loadContent($this->key);
    }

    protected function loadToc(): void
    {
		$projectPath = resource_path('help/toc.json');
		$packagePath = __DIR__ . '/../../../resources/help/toc.json';

		$path = file_exists($projectPath) ? $projectPath : $packagePath;

		if (!file_exists($path)) {
			$this->toc = [];
			return;
		}

		$raw = json_decode(file_get_contents($path), true) ?? [];
        $toc = [];
        $this->pageRoles = [];

        foreach ($raw as $title => $group) {
            $clean = [
                'roles' => $group['roles'] ?? null,
                'items' => [],
            ];

            foreach ($group['items'] ?? [] as $item) {
                $entry = [
                    'title'  => $item['title'] ?? null,
                    'roles'  => $item['roles'] ?? null,
                    'routes' => $item['routes'] ?? [],
                    'page'   => $item['page'] ?? null,
                ];

                if ($entry['page']) {
                    $this->pageRoles[$entry['page']] = $entry['roles'] ?? null;

                    $groupTitle = str_starts_with($title, '_') ? null : $title;

                    $this->pageMeta[$entry['page']] = [
                        'title' => $entry['title'] ?? $entry['page'],
                        'group' => $groupTitle,
                    ];
                }

                $clean['items'][] = $entry;
            }

            $toc[$title] = $clean;
        }

        $this->toc = $toc;
    }

    protected function resolveKey(): void
    {
        foreach ($this->toc as $group) {
            foreach ($group['items'] as $entry) {
                foreach ($entry['routes'] ?? [] as $r) {
                    if ($r === $this->key) {
                        $this->key = $entry['page'] ?? $entry['routes'][0];
                        return;
                    }
                }

                if (($entry['page'] ?? null) === $this->key) {
                    return;
                }
            }
        }
    }

    protected function loadContent(string $key): void
    {
        $roles = $this->pageRoles[$key] ?? null;

        if (!$this->hasRoleAccess($roles)) {
            $this->notFound = true;
            $this->html     = '<p class="text-muted">Kein Zugriff.</p>';
            return;
        }

        $projectFile = resource_path("help/{$key}.html");
        $packageFile = __DIR__ . '/../../../resources/help/' . $key . '.html';
        $file        = file_exists($projectFile) ? $projectFile : (file_exists($packageFile) ? $packageFile : null);

        if (!$file) {
            $this->notFound = true;
            $this->html     = '<p class="text-muted">Für diese Seite ist keine Hilfe verfügbar.</p>';
            return;
        }

        $html    = file_get_contents($file);
        $user    = auth()->user();
        $isAdmin = $user && (
            (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('admin'))
        );

        if ($isAdmin) {
            $html = preg_replace('/<admin-only>(.*?)<\/admin-only>/s', '$1', $html);
        } else {
            $html = preg_replace('/<admin-only>.*?<\/admin-only>/s', '', $html);
            $html = preg_replace('/<admin-note>.*?<\/admin-note>/s', '', $html);
        }

        $html = preg_replace_callback(
            '/<link\s+to="([^"]+)">(.+?)<\/link>/s',
            fn($m) => "<a href=\"/help/{$m[1]}\" style=\"color:inherit; text-decoration:underline;\">{$m[2]}</a>",
            $html
        );

        $this->html = $html;
    }

    public function hasRoleAccess(?array $roles): bool
    {
        if (!$roles || empty($roles)) return true;

        $user = auth()->user();
        if (!$user) return false;

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        if (in_array('admin', $roles) && method_exists($user, 'isAdmin')) {
            return $user->isAdmin();
        }

        return false;
    }

    public function updatedQuery(): void
    {
        $this->performSearch();
    }

    public function submitSearch(): void
    {
        $this->performSearch();
    }

    public function performSearch(): bool
    {
        $this->results = [];
        $term = trim($this->query);

        if (strlen($term) < 2) return false;

        $projectHelpPath = resource_path('help');
        $packageHelpPath = __DIR__ . '/../../../resources/help';

        $files = collect();

        // Package Help-Dateien laden
        if (is_dir($packageHelpPath)) {
            $files = $files->merge(
                collect(File::files($packageHelpPath))
                    ->filter(fn($f) => $f->getExtension() === 'html')
            );
        }

        // Projekt Help-Dateien laden – überschreiben Package bei gleichem Namen
        if (is_dir($projectHelpPath)) {
            $projectFiles = collect(File::files($projectHelpPath))
                ->filter(fn($f) => $f->getExtension() === 'html');

            $projectNames = $projectFiles->map(fn($f) => $f->getFilename())->toArray();
            $files = $files->filter(fn($f) => !in_array($f->getFilename(), $projectNames));
            $files = $files->merge($projectFiles);
        }

        // Nur Dateien die in toc.json erfasst sind
        $files = $files->filter(fn($f) => array_key_exists($f->getFilenameWithoutExtension(), $this->pageRoles));

        $user    = auth()->user();
        $isAdmin = $user && (
            (method_exists($user, 'isAdmin') && $user->isAdmin()) ||
            (method_exists($user, 'hasRole') && $user->hasRole('admin'))
        );

        foreach ($files as $file) {
            $name  = $file->getFilenameWithoutExtension();
            $roles = $this->pageRoles[$name] ?? null;

            if (!$this->hasRoleAccess($roles)) continue;

            $content = file_get_contents($file->getRealPath());

            if (!$isAdmin) {
                $content = preg_replace('/<admin-only>.*?<\/admin-only>/s', '', $content);
                $content = preg_replace('/<admin-note>.*?<\/admin-note>/s', '', $content);
            }

            $plain = strip_tags($content);
            $pos   = stripos($plain, $term);

            if ($pos === false) continue;

            $start   = max(0, $pos - 80);
            $excerpt = substr($plain, $start, strlen($term) + 160);
            $excerpt = preg_replace('/' . preg_quote($term, '/') . '/i', '<mark>$0</mark>', $excerpt);

            $meta  = $this->pageMeta[$name] ?? null;
            $label = $meta
                ? ($meta['group'] ? $meta['group'] . ' → ' . $meta['title'] : $meta['title'])
                : $name;

            $this->results[] = [
                'key'     => $name,
                'title'   => $label,
                'excerpt' => trim($excerpt),
            ];
        }

        return count($this->results) > 0;
    }

    public function goTo(string $target)
    {
        if (str_starts_with($target, 'page:')) {
            return redirect()->route('help.viewer', ['key' => substr($target, 5)]);
        }

        if (str_starts_with($target, 'route:')) {
            return redirect()->route('help.viewer', ['key' => substr($target, 6)]);
        }

        return redirect()->route('help.viewer', ['key' => $target]);
    }

    public function render()
    {
        return view('lara-base::livewire.help.viewer')
            ->layout('lara-base::layouts.help', [
                'pageTitle' => config('lara-base.help.title', config('app.name') . ' Hilfe'),
            ]);
    }
}