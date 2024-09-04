<x-filament::page>
    <div class="space-y-4">
        {{-- You can add custom components or HTML here --}}

        {{-- The table of records --}}
        {{ $this->table }}

        {{-- The form or actions to create a new certificate --}}
        {{ $this->form }}

        {{-- Any additional content or actions can go here --}}
    </div>
</x-filament::page>