<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'rich_text' => 'Rich Text',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
