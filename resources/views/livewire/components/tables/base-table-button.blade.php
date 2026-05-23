<div class="d-inline-flex align-items-center gap-0">
    @foreach ($buttons as $btn)
        @php
            $visible = $btn['showIf'] ?? true;
            if (is_callable($visible)) $visible = $visible($row);
            if (!$visible) continue;

            $idParam = $btn['idParam'] ?? 'id';
            $idValue = data_get($row, $idParam);
            $method  = $btn['method'] ?? null;
            $url     = $btn['url'] ?? null;
            $icon    = $btn['icon'] ?? 'mdi mdi-help';
            $title   = $btn['title'] ?? null;
            $attrs   = $btn['attrs'] ?? [];
        @endphp

        @if($method)
            <a href="javascript:void(0)"
               wire:click="{{ $method }}({{ $idValue }})"
               class="action-icon"
               @if($title) title="{{ $title }}" @endif>
                <i class="{{ $icon }}" wire:loading.remove wire:target="{{ $method }}({{ $idValue }})"></i>
                <span class="spinner-border spinner-border-sm text-secondary" role="status" wire:loading wire:target="{{ $method }}({{ $idValue }})"></span>
            </a>
        @elseif($url)
            <a href="{{ is_callable($url) ? $url($row) : $url }}"
               class="action-icon"
               @if($title) title="{{ $title }}" @endif>
                <i class="{{ $icon }}"></i>
            </a>
        @endif
    @endforeach
</div>