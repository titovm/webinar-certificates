<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use Filament\Tables\Table;
use App\Models\Certificate;
use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\SendCertificateMail;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Mail;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use App\Filament\Resources\CertificateResource;
use Filament\Pages\Actions\Action as PageAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\Action as TableAction;

class ViewCertificate extends Page implements HasTable
{
    protected static string $resource = CertificateResource::class;
    protected static string $view = 'filament.resources.certificates.view-certificate';
    use InteractsWithTable;

    protected $listeners = ['refreshTable' => '$refresh']; 

    public $certificate;

    public function mount($record)
    {
        $this->certificate = Certificate::findOrFail($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Participant::query()->where('certificate_id', $this->certificate->id))
            ->paginated(false)
            ->columns([
                TextColumn::make('name')
                    ->label('Имя')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('certificate_action')
                    ->label('Сертификат')
                    ->badge()
                    ->colors([
                        'success' => fn (Participant $record) => $record->certificate_url,
                        'primary' => fn (Participant $record) => !$record->certificate_url,
                    ])
                    ->icon(fn (Participant $record) => $record->certificate_url ? 'heroicon-s-document-arrow-down' : 'heroicon-s-document-plus')
                    ->iconPosition('before') // Position the icon before the text
                    ->getStateUsing(function (Participant $record) {
                        return $record->certificate_url ? 'Загрузить' : 'Создать';
                    })
                    ->url(fn (Participant $record) => $record->certificate_url ?: null)
                    ->openUrlInNewTab()
                    ->action(function (Participant $record) {
                        if (!$record->certificate_url) {
                            // Generate the certificate
                            $certificateService = new CertificateService();
                            $certificateUrl = $certificateService->generateAndStoreCertificate($record, $this->certificate);
    
                            // Update the participant's certificate_url
                            $record->update(['certificate_url' => $certificateUrl]);
    
                            // Send the certificate via email
                            Mail::to($record->email)->send(new SendCertificateMail($record));
    
                            Notification::make()
                                ->title('Сертификат успешно создан и отправлен!')
                                ->success()
                                ->send();
    
                            // Refresh the table to show the updated certificate URL
                            $this->emit('refreshTable');
                        }
                    }),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('Удалить')
                    ->before(function (Participant $record) {
                        if ($record->certificate_url) {
                            $filePath = str_replace('/storage/', '', $record->certificate_url);
                            Storage::disk('public')->delete($filePath);
                        }
                    })
                    ->successNotification(
                        Notification::make()
                            ->title('Участник удален')
                            ->body('Участник и его сертификат успешно удалены.')
                            ->success()
                    ),
                TableAction::make('emailCertificate')
                    ->label('Отправить')
                    ->action(function (Participant $record) {
                        Mail::to($record->email)->send(new SendCertificateMail($record));
                        Notification::make()
                            ->title('Сертификат отправлен')
                            ->body('Сертификат отправлен по адресу ' . $record->email)
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('bulkEmailCertificates')
                    ->label('Отправить сертификаты')
                    ->action(function (Collection $records) {  // Change to Collection
                        foreach ($records as $record) {
                            Mail::to($record->email)->send(new SendCertificateMail($record));
                        }
                        Notification::make()
                            ->title('Сертификаты отправлены')
                            ->body('Сертификаты отправлены выбраным участникам.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function getActions(): array
    {
        // Determine if the certificate is a module or acknowledgment
        $isModuleOrAcknowledgment = in_array($this->certificate->lecture_type, ['module', 'acknowledgment']);

        $actions = [
            PageAction::make('addParticipant')
                ->label('Добавить участника')
                ->form(function () use ($isModuleOrAcknowledgment) {
                    $fields = [
                        TextInput::make('name')->required()->label('Имя'),
                        TextInput::make('email')->required()->email()->label('Email'),
                    ];

                    if ($isModuleOrAcknowledgment) {
                        $fields = array_merge($fields, $this->getModuleOrAcknowledgmentFields());
                    }

                    return $fields;
                })
                ->action(function (array $data) use ($isModuleOrAcknowledgment) {
                    // Prepare additional data for module or acknowledgment
                    $participantData = [];

                    if ($isModuleOrAcknowledgment) {
                        if ($this->certificate->lecture_type === 'module') {
                            $participantData['certificate_number'] = $data['certificate_number'];
                            $participantData['date_1'] = $data['date_1'];
                            $participantData['date_2'] = $data['date_2'];
                            $participantData['hours'] = $data['hours'];
                        } elseif ($this->certificate->lecture_type === 'acknowledgment') {
                            $participantData['text'] = $data['text'];
                            $participantData['start_date'] = $data['start_date'];
                            $participantData['end_date'] = $data['end_date'];
                        }
                    }

                    // Create the participant
                    $participant = $this->certificate->participants()->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'data' => $participantData,
                        'certificate_url' => '',  // This will be updated after generating the PDF
                    ]);

                    // Generate the PDF certificate
                    $pdf = Pdf::loadView('certificates.pdf', [
                        'certificate' => $this->certificate,
                        'participant' => $participant,
                    ]);

                    // Save the PDF to storage
                    $fileName = 'certificates/' . $participant->id . '-certificate.pdf';
                    Storage::disk('public')->put($fileName, $pdf->output());

                    // Update the participant's certificate_url field with the storage URL
                    $participant->update([
                        'certificate_url' => Storage::url($fileName),
                    ]);

                    // Send the certificate email
                    Mail::to($participant->email)->send(new SendCertificateMail($participant));

                    // Trigger a success notification
                    Notification::make()
                        ->title('Участник добавлен, сертификат сгенерирован и отправлен по почте!')
                        ->success()
                        ->send();
                }),
        ];

        if (!$isModuleOrAcknowledgment) {
            // Add the importParticipants action only for webinar and event types
            $actions[] = PageAction::make('importParticipants')
                ->label('Импорт участников')
                ->form([
                    FileUpload::make('participants_csv')
                        ->label('Загрузить CSV с участниками')
                        ->acceptedFileTypes(['text/csv', 'text/plain', '.csv'])
                        ->maxSize(1024)  // Adjust file size limit if necessary
                        ->storeFiles(false)
                ])
                ->action(function (array $data) {
                    /** @var TemporaryUploadedFile $file */
                    $file = $data['participants_csv'];

                    // Process the CSV file directly using the Excel import
                    Excel::import(new ParticipantsImport($this->certificate), $file->getRealPath());

                    Notification::make()
                        ->title('Участники успешно добавлены!')
                        ->success()
                        ->send();
                });
        }

        return $actions;
    }

    protected function getModuleOrAcknowledgmentFields(): array
    {
        if ($this->certificate->lecture_type === 'module') {
            return [
                TextInput::make('certificate_number')->required()->label('Номер сертификата'),
                DatePicker::make('date_1')->required()->label('Дата 1'),
                DatePicker::make('date_2')->required()->label('Дата 2'),
                Select::make('hours')
                    ->label('Часы')
                    ->options([
                        '61' => '61 час',
                        '70' => '70 часов',
                    ])
                    ->required()
                    ->default('61'),
            ];
        } elseif ($this->certificate->lecture_type === 'acknowledgment') {
            return [
                TextInput::make('text')->required()->label('Текст благодарности'),
                DatePicker::make('start_date')->required()->label('Дата начала'),
                DatePicker::make('end_date')->required()->label('Дата окончания'),
            ];
        }

        return [];
    }
}