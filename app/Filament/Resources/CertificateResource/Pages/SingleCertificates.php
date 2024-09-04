<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use Filament\Tables\Table;
use App\Models\Certificate;
use App\Models\Participant;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\SendCertificateMail;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CertificateResource;

class SingleCertificates extends ListRecords
{
    protected static ?string $title = 'Single Certificates';

    protected static string $resource = CertificateResource::class;

    /**
     * Query to list only 'module' and 'acknowledgment' certificate types.
     */
    protected function getTableQuery(): ?Builder
    {
        return Certificate::query()->whereIn('lecture_type', ['module', 'acknowledgment']);
    }

    /**
     * Define the table that lists the single certificates.
     */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Название'),
                TextColumn::make('lecture_type')->label('Тип'),
                TextColumn::make('created_at')->label('Создано')->date(),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('Удалить')
                    ->before(function (Certificate $record) {
                        // Handle deletion if needed, e.g., removing associated files.
                        if ($record->participants) {
                            foreach ($record->participants as $participant) {
                                if ($participant->certificate_url) {
                                    $filePath = str_replace('/storage/', '', $participant->certificate_url);
                                    Storage::disk('public')->delete($filePath);
                                }
                            }
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->title('Сертификат удален')
                            ->body('Сертификат успешно удален.')
                            ->success()
                    ),
            ]);
    }

    /**
     * Define actions like creating a new single certificate.
     */
    protected function getActions(): array
    {
        return [
            Action::make('addCertificate')
                ->label('Создать сертификат')
                ->form($this->formSchema())
                ->action(fn (array $data) => $this->submit($data)) // Use a closure to handle the form submission
                ->modalHeading('Создать новый сертификат')
                ->modalWidth('lg'),
        ];
    }

    /**
     * Define the form schema for creating a single certificate.
     */
    protected function formSchema(): array
    {
        return [
            Select::make('certificate_type')
                ->label('Тип сертификата')
                ->options([
                    'module' => 'Модуль',
                    'acknowledgment' => 'Благодарность',
                ])
                ->required()
                ->reactive(),  // Make the select field reactive

            TextInput::make('name')
                ->label('Имя участника')
                ->required(),

            TextInput::make('email')
                ->label('Email участника')
                ->email()
                ->required(),

            Select::make('hours')
                ->label('Часы')
                ->options([
                    '61' => '61 час',
                    '70' => '70 часов',
                ])
                ->visible(fn ($get) => $get('certificate_type') === 'module')  // Show only for acknowledgment
                ->required(fn ($get) => $get('certificate_type') === 'module'), // Make required if visible

            TextInput::make('certificate_number')
                ->label('Номер Сертификата')
                ->visible(fn ($get) => $get('certificate_type') === 'module')  // Show only for acknowledgment
                ->required(fn ($get) => $get('certificate_type') === 'module'), // Make required if visible

            DatePicker::make('date_1')
                ->label('Дата 1')
                ->visible(fn ($get) => $get('certificate_type') === 'module')  // Show only for module
                ->required(fn ($get) => $get('certificate_type') === 'module'), // Make required if visible

            DatePicker::make('date_2')
                ->label('Дата 2')
                ->visible(fn ($get) => $get('certificate_type') === 'module')  // Show only for module
                ->required(fn ($get) => $get('certificate_type') === 'module'), // Make required if visible

            Textarea::make('text')
                ->label('Текст благодарности')
                ->visible(fn ($get) => $get('certificate_type') === 'acknowledgment')  // Show only for acknowledgment
                ->required(fn ($get) => $get('certificate_type') === 'acknowledgment'), // Make required if visible

            DatePicker::make('start_date')
                ->label('Дата начала')
                ->visible(fn ($get) => $get('certificate_type') === 'acknowledgment')  // Show only for acknowledgment
                ->required(fn ($get) => $get('certificate_type') === 'acknowledgment'), // Make required if visible

            DatePicker::make('end_date')
                ->label('Дата окончания')
                ->visible(fn ($get) => $get('certificate_type') === 'acknowledgment')  // Show only for acknowledgment
                ->required(fn ($get) => $get('certificate_type') === 'acknowledgment'), // Make required if visible
        ];
    }

    /**
     * Handle the submission of the form to create a new single certificate.
     */
    protected function submit(array $data): void
    {
        $certificate = Certificate::create([
            'name' => $data['name'],
            'lecture_type' => $data['certificate_type'],
            'data' => $data,
        ]);

        $participant = $certificate->participants()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'data' => $data,
            'certificate_url' => '',
        ]);

        $pdf = Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'participant' => $participant,
        ]);

        $fileName = 'certificates/' . $participant->id . '-certificate.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        $participant->update([
            'certificate_url' => Storage::url($fileName),
        ]);

        Mail::to($participant->email)->send(new SendCertificateMail($participant));

        Notification::make()
            ->title('Сертификат создан и отправлен!')
            ->success()
            ->send();
    }
}