<div>
    <div class="accordion mb-3" id="accordionChangelog">
        @foreach($entries as $index => $entry)
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{ $index }}">
                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $index }}"
                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                            aria-controls="collapse{{ $index }}">
                        Version {{ $entry['version'] }} vom {{ $entry['date'] }}
                    </button>
                </h2>
                <div id="collapse{{ $index }}"
                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                     aria-labelledby="heading{{ $index }}"
                     data-bs-parent="#accordionChangelog">
                    <div class="accordion-body border-top">
                        {!! $entry['body'] !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>