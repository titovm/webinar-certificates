<?php

namespace App\Filament\Resources\CertificateResource\Pages;

use Filament\Tables\Table;
use Livewire\Component;
use App\Models\Certificate;
use App\Models\Participant;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\SendCertificateMail;
use Filament\Resources\Pages\Page;
use App\Imports\ParticipantsImport;
use Filament\Forms\Components\Card;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\UrlColumn;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Services\CertificateService;
use App\Filament\Resources\CertificateResource;
use Filament\Pages\Actions\Action as PageAction;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\Action as TableAction;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

                BadgeColumn::make('certificate_url')
                    ->label('Сертификат')
                    ->colors([
                        'success' => fn (Participant $record) => $record->certificate_url,  // Green for "Download"
                        'primary' => fn (Participant $record) => !$record->certificate_url, // Red for "Generate"
                    ])
                    ->getStateUsing(function (Participant $record) {
                        return $record->certificate_url ? 'Загрузить' : 'Создать';
                    })
                    ->action(function (Participant $record) {
                        if ($record->certificate_url) {
                            return redirect()->to($record->certificate_url);
                        } else {
                            $certificateService = new CertificateService();
                            $certificateUrl = $certificateService->generateAndStoreCertificate($record, $this->certificate);

                            Mail::to($record->email)->send(new SendCertificateMail($record));

                            Notification::make()
                                ->title('Сертификат успешно создан и отправлен!')
                                ->success()
                                ->send();
                        }
                    })
                    ->icon(fn (Participant $record) => $record->certificate_url ? 'heroicon-s-document-arrow-down' : 'heroicon-s-document-plus'),
            ])
            ->actions([
                DeleteAction::make()
                    // ->confirm('Вы уверены, что хотите удалить этого участника и его сертификат?')
                    // ->confirmButtonText('Да, удалить')
                    // ->cancelButtonText('Отмена')
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
        return [
            PageAction::make('addParticipant')
                ->label('Добавить участника')
                ->form([
                    TextInput::make('name')->required()->label('Имя'),
                    TextInput::make('email')->required()->email()->label('Email'),
                ])
                ->action(function (array $data) {
                    // Create the participant
                    $participant = $this->certificate->participants()->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
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
            PageAction::make('importParticipants')
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
                }),
            
        ];
    }


}