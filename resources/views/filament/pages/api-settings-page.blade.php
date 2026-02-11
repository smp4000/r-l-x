<x-filament-panels::page>
    <x-filament-schemas::form wire:submit="save">
        {{ $this->schema('form') }}

        <x-slot name="actions">
            {{ $this->getFormActions() }}
        </x-slot>
    </x-filament-schemas::form>
</x-filament-panels::page>
