<x-filament::page>
    <div class="space-y-4">
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-medium mb-6">{{ $certificate->webinar_name }}</h2>
            </div>
            <div class="card-body">
                <p><strong>Ведущий:</strong> {{ $certificate->lecturer_name }}</p>
                <p><strong>Дата:</strong> {{ $certificate->date }}</p>
                <p><strong>Тип лекции:</strong> {{ $certificate->lecture_type }}</p>
                <p><strong>Часов:</strong> {{ $certificate->hours ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="card-body">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-lg font-medium mb-6">Участники</h2>
                </div>
                <div class="card-body">
                    {{ $this->table }}
                </div>
            </div>
        </div>
    </div>
</x-filament::page>