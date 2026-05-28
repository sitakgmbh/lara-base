<div>
    @foreach($groups as $group => $items)
        <h5 class="{{ !$loop->first ? 'mt-4' : '' }} mb-3">
            {{ $group }}
        </h5>

        <div class="row g-3">
            @foreach($items as $tool)
                <div class="col-12 col-md-6 col-lg-3">
                    <a href="{{ $tool['is_external'] ? url($tool['route']) : route($tool['route']) }}"
                       class="text-decoration-none text-dark"
                       target="{{ $tool['is_external'] ? '_blank' : '_self' }}">
                        <div class="d-flex align-items-center border rounded p-3 h-100 bg-body-secondary">
                            <i class="{{ $tool['icon'] }} text-{{ $tool['color'] }}"
                               style="font-size: 2.5rem; min-width: 45px;"></i>
                            <div class="ms-3 d-flex flex-column justify-content-center">
                                <span class="fw-bold">{{ $tool['title'] }}</span>
                                <small class="text-muted">{{ $tool['description'] }}</small>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="mb-3"></div>
</div>
