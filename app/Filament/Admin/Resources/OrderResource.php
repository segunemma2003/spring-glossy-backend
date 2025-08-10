<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Colors\Color;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Sales';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->disabled(),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'email')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) =>
                                "{$record->full_name} ({$record->email})"
                            ),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'paystack' => 'Paystack',
                                'transfer' => 'Bank Transfer',
                            ]),
                        Forms\Components\DateTimePicker::make('paid_at'),
                    ])->columns(2),

                Forms\Components\Section::make('Payment Receipt')
                    ->schema([
                        Forms\Components\FileUpload::make('payment_receipt_path')
                            ->label('Payment Receipt')
                            ->image()
                            ->disk('s3')
                            ->directory('receipts')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Forms\Get $get): bool => $get('payment_method') === 'transfer'),

                Forms\Components\Section::make('Amounts')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled(),
                        Forms\Components\TextInput::make('tax_amount')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled(),
                        Forms\Components\TextInput::make('shipping_fee')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled(),
                        Forms\Components\TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('₦')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Shipping Information')
                    ->schema([
                        Forms\Components\KeyValue::make('shipping_address')
                            ->columnSpanFull()
                            ->disabled(),
                    ]),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Customer Notes')
                            ->disabled(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Admin Notes'),
                    ]),

                Forms\Components\Section::make('Order Items')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('product_name')
                                    ->disabled(),
                                Forms\Components\TextInput::make('product_sku')
                                    ->disabled(),
                                Forms\Components\TextInput::make('quantity')
                                    ->numeric()
                                    ->disabled(),
                                Forms\Components\TextInput::make('price')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->disabled(),
                                Forms\Components\TextInput::make('total')
                                    ->numeric()
                                    ->prefix('₦')
                                    ->disabled(),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.full_name')
                    ->label('Customer')
                    ->searchable(['users.first_name', 'users.last_name'])
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'processing',
                        'primary' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ]),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'failed',
                        'secondary' => 'refunded',
                    ]),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paystack' => 'success',
                        'transfer' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('payment_receipt_path')
                    ->label('Receipt')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-mark')
                    ->tooltip(fn ($record) => $record->payment_receipt_path ? 'Receipt uploaded' : 'No receipt'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('NGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'paystack' => 'Paystack',
                        'transfer' => 'Bank Transfer',
                    ]),
                Tables\Filters\Filter::make('has_receipt')
                    ->query(fn ($query) => $query->whereNotNull('payment_receipt_path'))
                    ->label('Has Receipt'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('view_receipt')
                    ->label('View Receipt')
                    ->icon('heroicon-o-document-text')
                    ->url(fn (Order $record): ?string => $record->payment_receipt_url)
                    ->openUrlInNewTab()
                    ->visible(fn (Order $record): bool => !empty($record->payment_receipt_path)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_processing')
                        ->label('Mark as Processing')
                        ->icon('heroicon-o-arrow-path')
                        ->action(fn ($records) => $records->each->update(['status' => 'processing'])),
                    Tables\Actions\BulkAction::make('mark_as_shipped')
                        ->label('Mark as Shipped')
                        ->icon('heroicon-o-truck')
                        ->action(fn ($records) => $records->each->update(['status' => 'shipped'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
