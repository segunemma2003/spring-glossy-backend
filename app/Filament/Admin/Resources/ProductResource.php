<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Inventory';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Product Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, Forms\Set $set) =>
                                $context === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('₦'),
                    ])->columns(2),

                Forms\Components\Section::make('Images')
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->reorderable()
                            ->disk('s3')
                            ->directory('products')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '1:1',
                                '4:3',
                                '16:9',
                            ])
                            ->resize(800, 800)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Categories & Properties')
                    ->schema([
                        Forms\Components\TagsInput::make('category')
                            ->suggestions(['bestseller', 'shimmer', 'tinted', 'clear', 'moisturizing', 'plumping', 'holographic', 'scented']),
                        Forms\Components\TagsInput::make('colors')
                            ->placeholder('Enter color hex codes like #FFB6C1'),
                        Forms\Components\Toggle::make('is_new'),
                        Forms\Components\Toggle::make('is_best_seller'),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Inventory & Details')
                    ->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('weight')
                            ->suffix('g'),
                        Forms\Components\TextInput::make('dimensions')
                            ->placeholder('L x W x H (cm)'),
                        Forms\Components\TextInput::make('sku')
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images')
                    ->label('Image')
                    ->getStateUsing(fn (Product $record): ?string =>
                        $record->images && !empty($record->images)
                            ? Storage::disk('s3')->url($record->images[0])
                            : null
                    )
                    ->size(60)
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TagsColumn::make('category'),
                Tables\Columns\IconColumn::make('is_new')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_best_seller')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state > 20 => 'success',
                        $state > 5 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
                Tables\Filters\TernaryFilter::make('is_new'),
                Tables\Filters\TernaryFilter::make('is_best_seller'),
                Tables\Filters\Filter::make('low_stock')
                    ->query(fn ($query) => $query->where('stock_quantity', '<', 10))
                    ->label('Low Stock'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('toggle_active')
                        ->label('Toggle Active Status')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['is_active' => !$record->is_active]);
                            }
                        }),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
