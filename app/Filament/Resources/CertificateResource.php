<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Certificate;
use Filament\Resources\Resource;
use App\Imports\ParticipantsImport;
use Filament\Tables\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\CertificateResource\Pages;
use App\Filament\Resources\CertificateResource\RelationManagers;
use App\Filament\Resources\CertificateResource\Pages\ViewCertificate;

class CertificateResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static ?string $navigationIcon = 'heroicon-c-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('webinar_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('lecturer_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\Select::make('lecture_type')
                    ->options([
                        'Webinar' => 'Webinar',
                        'Module' => 'Module',
                        'Masterclass' => 'Masterclass',
                    ])
                    ->default('Webinar')
                    ->required(),
                Forms\Components\TextInput::make('hours')
                    ->numeric()
                    ->nullable(),
                FileUpload::make('participants_csv')
                    ->label('Upload Participants CSV')
                    ->acceptedFileTypes(['text/csv', 'text/plain', '.csv'])
                    ->maxSize(1024)  // Adjust file size limit if necessary
                    ->afterStateUpdated(function ($state, $get, $set, $record) {
                        if ($state && $record) {
                            $certificate = $record;
    
                            // Process the CSV file without saving it to storage
                            Excel::import(new ParticipantsImport($certificate), $state);
    
                            // Delete the temporary file after processing
                            Storage::disk('local')->delete($state);
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('webinar_name')
                    ->label('Название вебинара')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('lecturer_name')
                    ->label("Имя лектора")
                    ->sortable()
                    ->searchable(),
                TextColumn::make('date')
                    ->label('Дата')
                    ->sortable(),
                TextColumn::make('lecture_type')
                    ->label("Тип лекции")
                    ->sortable(),
                TextColumn::make('total_participants')
                    ->label('Всего участников')
                    ->counts('participants') // Use the counts method to count related participants
                    ->getStateUsing(fn (Certificate $record) => $record->participants()->count())
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('view')
                    ->label('View')
                    ->url(fn (Certificate $record) => static::getUrl('view', ['record' => $record->getKey()]))
                    ->icon('heroicon-c-eye'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/'),
            'create' => Pages\CreateCertificate::route('/create'),
            'edit' => Pages\EditCertificate::route('/{record}/edit'),
            'view' => ViewCertificate::route('/{record}/view'),
        ];
    }
}
