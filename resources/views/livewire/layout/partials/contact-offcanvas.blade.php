@if(config('lara-base.company.name') || config('lara-base.company.phone') || config('lara-base.company.email'))
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasHelp" aria-labelledby="offcanvasHelpLabel">
        <div class="offcanvas-header">
            <h3 id="offcanvasHelpLabel">Kontakt</h3>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            {{ config('lara-base.contact.intro', 'Bei Fragen oder Problemen wenden Sie sich bitte an:') }}
            <div class="border rounded p-3 bg-light mt-2 shadow-sm">
                @if(config('lara-base.company.name'))
                    <div class="mb-1">
                        <strong>{{ config('lara-base.company.name') }}</strong>
                    </div>
                @endif
                @if(config('lara-base.company.phone'))
                    <div>
                        <a href="tel:{{ config('lara-base.company.phone') }}" class="text-decoration-none link-secondary">
                            {{ config('lara-base.company.phone') }}
                        </a>
                    </div>
                @endif
                @if(config('lara-base.company.email'))
                    <div>
                        <a href="mailto:{{ config('lara-base.company.email') }}" class="text-decoration-none link-secondary">
                            {{ config('lara-base.company.email') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif