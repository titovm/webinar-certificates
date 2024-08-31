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
use Filament\Support\Enums\FontWeight;
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
                Forms\Components\TextInput::make('name')
                    ->label('Название')
                    ->required(),

                Forms\Components\Select::make('lecture_type')
                    ->label('Тип лекции')
                    ->options([
                        'webinar' => 'Вебинар',
                        'event' => 'Мероприятие',
                        'module' => 'Модуль',
                        'acknowledgment' => 'Благодарность',
                    ])
                    ->required()
                    ->reactive(),

                Forms\Components\TextInput::make('data.lecturer_name')
                    ->label('Ведущий')
                    ->visible(fn ($get) => $get('lecture_type') === 'webinar'),

                Forms\Components\DatePicker::make('data.date')
                    ->label('Дата')
                    ->visible(fn ($get) => in_array($get('lecture_type'), ['webinar', 'event'])),

                Forms\Components\TextInput::make('data.hours')
                    ->label('Часов')
                    ->visible(fn ($get) => $get('lecture_type') === 'webinar'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lecture_type')
                    ->label("Тип лекции")
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Название вебинара')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->description(function (Certificate $record) {
                        if ($record->lecture_type === 'webinar') {
                            return 'Ведущий: ' . ($record->data['lecturer_name'] ?? 'N/A');
                        } elseif ($record->lecture_type === 'module') {
                            return "Модуль";
                        } else {
                            $startDate = $record->data['start_date'] ?? 'N/A';
                            $endDate = $record->data['end_date'] ?? 'N/A';
                            return "Начало: $startDate, Конец: $endDate";
                        }
                    }),
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
