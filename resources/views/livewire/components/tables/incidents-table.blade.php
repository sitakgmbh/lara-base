<div>
    @include('lara-base::livewire.components.tables.base-table', ['columns' => $columns, 'records' => $records])
	<livewire:components.modals.confirm-modal />
</div>