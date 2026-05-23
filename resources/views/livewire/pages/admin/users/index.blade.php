<div>
    @section('pageActions')
        @if(config('lara-base.auth.mode') === 'local')
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary" title="Benutzer erstellen">
                <i class="mdi mdi-account-plus"></i>
            </a>
        @endif
    @endsection

    <livewire:tables.users-table />
</div>