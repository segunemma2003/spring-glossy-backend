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
                Section::make('Setting Information')
                    ->schema([
                        Select::make('key')
                            ->label('Setting Key')
                            ->options([
                                'caption' => 'Business Caption',
                                'instagram_link' => 'Instagram Link',
                                'facebook_link' => 'Facebook Link',
                                'twitter_link' => 'Twitter Link',
                                'phone_number' => 'Phone Number',
                                'whatsapp_number' => 'WhatsApp Number',
                                'official_email' => 'Official Email',
                                'address' => 'Business Address',
                                'location' => 'Business Location',
                                'business_hours' => 'Business Hours',
                                'my_story' => 'My Story',
                                'privacy_policy' => 'Privacy Policy',
                                'terms_of_service' => 'Terms of Service',
                                'cookie_policy' => 'Cookie Policy',
                            ])
                            ->required()
                            ->searchable()
                            ->unique(ignoreRecord: true),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'string' => 'String',
                                'text' => 'Text',
                                'rich_text' => 'Rich Text',
                                'json' => 'JSON',
                            ])
                            ->default('string')
                            ->required(),

                        Textarea::make('value')
                            ->label('Value')
                            ->placeholder('Enter the setting value')
                            ->rows(4)
                            ->required(),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Setting')
                    ->formatStateUsing(function ($state) {
                        $labels = [
                            'caption' => 'Business Caption',
                            'instagram_link' => 'Instagram Link',
                            'facebook_link' => 'Facebook Link',
                            'twitter_link' => 'Twitter Link',
                            'phone_number' => 'Phone Number',
                            'whatsapp_number' => 'WhatsApp Number',
                            'official_email' => 'Official Email',
                            'address' => 'Business Address',
                            'location' => 'Business Location',
                            'business_hours' => 'Business Hours',
                            'my_story' => 'My Story',
                            'privacy_policy' => 'Privacy Policy',
                            'terms_of_service' => 'Terms of Service',
                            'cookie_policy' => 'Cookie Policy',
                        ];
                        return $labels[$state] ?? $state;
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Value')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'string' => 'String',
                        'text' => 'Text',
                        'rich_text' => 'Rich Text',
                        'json' => 'JSON',
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
