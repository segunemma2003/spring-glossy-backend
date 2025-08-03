<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getSettingsData());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Information')
                    ->schema([
                        TextInput::make('caption')
                            ->label('Caption')
                            ->placeholder('Enter your business caption')
                            ->maxLength(255),

                        TextInput::make('instagram_link')
                            ->label('Instagram Link')
                            ->url()
                            ->placeholder('https://instagram.com/yourbusiness'),

                        TextInput::make('facebook_link')
                            ->label('Facebook Link')
                            ->url()
                            ->placeholder('https://facebook.com/yourbusiness'),

                        TextInput::make('twitter_link')
                            ->label('Twitter Link')
                            ->url()
                            ->placeholder('https://twitter.com/yourbusiness'),
                    ])->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->tel()
                            ->placeholder('+234 123 456 7890'),

                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp Number')
                            ->tel()
                            ->placeholder('+234 123 456 7890'),

                        TextInput::make('official_email')
                            ->label('Official Email')
                            ->email()
                            ->placeholder('info@springglossy.com.ng'),

                        TextInput::make('address')
                            ->label('Address')
                            ->placeholder('Enter your business address')
                            ->maxLength(500),
                    ])->columns(2),

                Section::make('Business Hours')
                    ->schema([
                        RichEditor::make('business_hours')
                            ->label('Business Hours')
                            ->placeholder('Enter your business hours information')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('My Story')
                    ->schema([
                        RichEditor::make('my_story')
                            ->label('My Story')
                            ->placeholder('Tell your business story')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Legal Documents')
                    ->schema([
                        RichEditor::make('privacy_policy')
                            ->label('Privacy Policy')
                            ->placeholder('Enter your privacy policy content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'h4',
                            ])
                            ->columnSpanFull(),

                        RichEditor::make('terms_of_service')
                            ->label('Terms of Service')
                            ->placeholder('Enter your terms of service content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'h4',
                            ])
                            ->columnSpanFull(),

                        RichEditor::make('cookie_policy')
                            ->label('Cookie Policy')
                            ->placeholder('Enter your cookie policy content')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'h4',
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Settings')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if ($value !== null) {
                Setting::setValue($key, $value);
            }
        }

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }

    private function getSettingsData(): array
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        return [
            'caption' => $settings['caption'] ?? '',
            'instagram_link' => $settings['instagram_link'] ?? '',
            'facebook_link' => $settings['facebook_link'] ?? '',
            'twitter_link' => $settings['twitter_link'] ?? '',
            'phone_number' => $settings['phone_number'] ?? '',
            'whatsapp_number' => $settings['whatsapp_number'] ?? '',
            'official_email' => $settings['official_email'] ?? '',
            'address' => $settings['address'] ?? '',
            'business_hours' => $settings['business_hours'] ?? '',
            'my_story' => $settings['my_story'] ?? '',
            'privacy_policy' => $settings['privacy_policy'] ?? '',
            'terms_of_service' => $settings['terms_of_service'] ?? '',
            'cookie_policy' => $settings['cookie_policy'] ?? '',
        ];
    }
}
