<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class OrderResource extends Resource
{
    use \App\Traits\HasNavigationBadge;

    protected static ?string $model = Order::class; // penyebab eror karena

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Information')->schema([
                    Forms\Components\TextInput::make('order_number')
                        ->label('Order Number')
                        ->required()
                        ->default(generateSequentialNumber(Order::class))
                        ->readOnly(),

                    Forms\Components\TextInput::make('order_name')
                        ->label('Order Name')
                        ->maxLength(255)
                        ->placeholder('Enter order name'),

                    Forms\Components\TextInput::make('total')
                        ->label('Total Amount')
                        ->readOnlyOn('create')
                        ->default(0)
                        ->numeric(),

                        Forms\Components\Select::make('customer_id')
                            ->label('Select or Add Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Customer Name')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->maxLength(255)
                                    ->required(), // Email is required, so we add this

                                Forms\Components\TextInput::make('phone_number')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('Enter phone number'),

                                Forms\Components\Textarea::make('address')
                                    ->label('Address')
                                    ->placeholder('Enter customer address...')
                            ])
                            ->placeholder('Select an existing customer or add a new one'),

                    Forms\Components\Group::make([
                        Forms\Components\Select::make('payment_method')
                            ->label('Payment Method')
                            ->enum(\App\Enums\PaymentMethod::class)
                            ->options(\App\Enums\PaymentMethod::class)
                            ->default(\App\Enums\PaymentMethod::CASH)
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Order Status')
                            ->required()
                            ->enum(\App\Enums\OrderStatus::class)
                            ->options(\App\Enums\OrderStatus::class)
                            ->default(\App\Enums\OrderStatus::PENDING),
                    ])->columnSpan(2)->columns(2),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns(self::getTableColumns())
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(\App\Enums\OrderStatus::class),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->multiple()
                    ->options(\App\Enums\PaymentMethod::class),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->maxDate(fn (Forms\Get $get) => $get('end_date') ?: now())
                            ->native(false),
                        Forms\Components\DatePicker::make('created_until')
                            ->native(false)
                            ->maxDate(now()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->button()
                    ->color('gray')
                    ->icon('heroicon-o-printer')
                    ->action(function (Order $record) {
                        $pdf = Pdf::loadView('pdf.print-order', [
                            'order' => $record,
                        ]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'receipt-' . $record->order_number . '.pdf');
                    }),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('gray'),
                    Tables\Actions\EditAction::make()
                        ->color('gray'),
                    Tables\Actions\Action::make('edit-transaction')
                        ->visible(fn (Order $record) => $record->status === \App\Enums\OrderStatus::PENDING)
                        ->label('Edit Transaction')
                        ->icon('heroicon-o-pencil')
                        ->url(fn ($record) => "/orders/{$record->order_number}"),
                    Tables\Actions\Action::make('mark-as-complete')
                        ->visible(fn (Order $record) => $record->status === \App\Enums\OrderStatus::PENDING)
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Order $record) => $record->markAsComplete())
                        ->label('Mark as Complete'),
                    Tables\Actions\Action::make('divider')->label('')->disabled(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function (Order $order) {
                            $order->orderDetails()->delete();
                            $order->delete();
                })
            ])
                ->color('gray'),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (\Illuminate\Support\Collection $records) {
                            $records->each(fn (Order $order) => $order->orderDetails()->delete());
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\ExportAction::make()
                    ->label('Export Excel')
                    ->fileDisk('public')
                    ->color('success')
                    ->icon('heroicon-o-document-text')
                    ->exporter(\App\Filament\Exports\OrderExporter::class),
            ]);

    }


    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\OrderResource\RelationManagers\OrderDetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'create-transaction' => Pages\CreateTransaction::route('{record}'),
        ];
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist->schema([
            TextEntry::make('order_number')->color('gray'),
            TextEntry::make('customer.name')->placeholder('-'),
            TextEntry::make('discount')->money('IDR')->color('gray'),
            TextEntry::make('total')->money('IDR')->color('gray'),
            TextEntry::make('payment_method')->badge()->color('gray'),
            TextEntry::make('status')->badge()->color(fn ($state) => $state->getColor()),
            TextEntry::make('created_at')->dateTime()->formatStateUsing(fn ($state) => $state->format('d M Y H:i'))->color('gray'),
        ]);
    }

    public static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('order_number')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('order_name')
                ->searchable(),
            Tables\Columns\TextColumn::make('discount')
                ->numeric()
                ->sortable()
                ->formatStateUsing(fn ($state) => $state . '%'),
            Tables\Columns\TextColumn::make('total')
                ->numeric()
                ->alignEnd()
                ->sortable()
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                ->summarize(
                    Tables\Columns\Summarizers\Sum::make('total')
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
                ),

            Tables\Columns\TextColumn::make('profit')
                ->numeric()
                ->alignEnd()
                ->sortable()
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                ->summarize(
                    Tables\Columns\Summarizers\Sum::make('profit')
                        ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
                ),

            \Filament\Tables\Columns\TextColumn::make('customer_cash')
                ->label('Customer Cash')
                ->sortable()
                ->money('IDR')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

            \Filament\Tables\Columns\TextColumn::make('change')
                ->label('Change')
                ->sortable()
                ->money('IDR')
                ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),
            Tables\Columns\TextColumn::make('payment_method')
                ->badge()
                ->color('gray'),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn($state) => $state->getColor()),

            Tables\Columns\TextColumn::make('user.name')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('customer.name')
                ->numeric()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->formatStateUsing(fn($state) => $state->format('d M Y H:i')),
            Tables\Columns\TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->formatStateUsing(fn($state) => $state->format('d M Y H:i'))
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            OrderResource\Widgets\OrderStats::class,
        ];
    }


}
