<x-filament::page>
    <div class="space-y-4">
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-medium mb-6">{{ $certificate->name }}</h2>
            </div>
            <div class="card-body">
                <p><strong>Тип лекции:</strong> {{ ucfirst($certificate->lecture_type) }}</p>

                @if ($certificate->lecture_type === 'webinar')
                    <p><strong>Ведущий:</strong> {{ $certificate->data['lecturer_name'] ?? 'N/A' }}</p>
                    <p><strong>Дата:</strong> {{ $certificate->data['date'] ?? 'N/A' }}</p>
                    <p><strong>Часов:</strong> {{ $certificate->data['hours'] ?? 'N/A' }}</p>
                @elseif ($certificate->lecture_type === 'event')
                    <p><strong>Дата начала:</strong> {{ $certificate->data['start_date'] ?? 'N/A' }}</p>
                    <p><strong>Дата окончания:</strong> {{ $certificate->data['end_date'] ?? 'N/A' }}</p>
                @elseif ($certificate->lecture_type === 'module')
                    {{-- <p><strong>Номер сертификата:</strong> {{ $certificate->data['certificate_number'] ?? 'N/A' }}</p>
                    <p><strong>Дата 1:</strong> {{ $certificate->data['date_1'] ?? 'N/A' }}</p>
                    <p><strong>Дата 2:</strong> {{ $certificate->data['date_2'] ?? 'N/A' }}</p> --}}
                @elseif ($certificate->lecture_type === 'acknowledgment')
                    {{-- <p><strong>Текст:</strong> {{ $certificate->data['text'] ?? 'N/A' }}</p>
                    <p><strong>Дата начала:</strong> {{ $certificate->data['start_date'] ?? 'N/A' }}</p>
                    <p><strong>Дата окончания:</strong> {{ $certificate->data['end_date'] ?? 'N/A' }}</p> --}}
                @endif
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