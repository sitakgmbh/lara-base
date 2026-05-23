<div>
    <div class="card">
        <div class="card-header bg-primary text-white py-1">
            <p class="mb-0"><strong>Log #{{ $log->id }}</strong></p>
        </div>
        <div class="card-body">
            <ul class="list-unstyled mb-3">
                <li><strong>Level:</strong> {{ $log->level->label() }}</li>
                <li><strong>Kategorie:</strong> {{ $log->category->label() }}</li>
                <li><strong>Erstellt am:</strong> {{ $log->created_at->format('d.m.Y H:i:s') }}</li>
                <li><strong>Nachricht:</strong> {{ $log->message }}</li>
            </ul>

            <h6>Kontext:</h6>
            <pre class="bg-light p-3 rounded"
                 style="font-size: 0.85rem; white-space: pre;">
{!! json_encode(json_decode($log->context, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}
            </pre>

            <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary btn-sm mt-2">
                <i class="mdi mdi-arrow-left"></i> Zurück
            </a>
        </div>
    </div>
</div>