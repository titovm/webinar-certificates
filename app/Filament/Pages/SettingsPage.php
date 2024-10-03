<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use App\Models\Setting;
use Filament\Pages\Actions\ButtonAction;
use Filament\Notifications\Notification;

class SettingsPage extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.settings-page';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'Settings';
    protected static ?int $navigationSort = 100;

    public $email_sending_enabled;
    public $email_from_name;
    public $email_from_address;

    public function mount()
    {
        // Load existing settings
        $this->email_sending_enabled = Setting::getValue('email_sending_enabled', true);
        $this->email_from_name = Setting::getValue('email_from_name', config('mail.from.name'));
        $this->email_from_address = Setting::getValue('email_from_address', config('mail.from.address'));
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Email Settings')
                ->schema([
                    Toggle::make('email_sending_enabled')
                        ->label('Enable Email Sending')
                        ->default(true),
                    TextInput::make('email_from_name')
                        ->label('From Name')
                        ->required(),
                    TextInput::make('email_from_address')
                        ->label('From Address')
                        ->email()
                        ->required(),
                ]),
        ];
    }

    public function submit()
    {
        // Save the settings
        Setting::setValue('email_sending_enabled', $this->email_sending_enabled);
        Setting::setValue('email_from_name', $this->email_from_name);
        Setting::setValue('email_from_address', $this->email_from_address);

        Notification::make()
            ->title('Настройки сохранены.')
            ->success()
            ->send();
    }

    protected function getFormModel(): string
    {
        return static::class;
    }

    protected function getActions(): array
    {
        return [
            ButtonAction::make('save')
                ->label('Save Settings')
                ->action('submit')
                ->color('primary'),
        ];
    }
}