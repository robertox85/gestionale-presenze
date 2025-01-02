<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PresenzaResource\Pages;
use App\Filament\Resources\PresenzaResource\RelationManagers;
use App\Models\Presenza;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PresenzaResource extends Resource
{
    protected static ?string $model = Presenza::class;
    protected static ?string $slug = 'presenze';
    protected static ?string $label = 'Presenze';
    protected static ?string $navigationLabel = 'Presenze';
    protected static ?string $pluralLabel = 'Presenze';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('anagrafica_id')
                    ->relationship('anagrafica', 'nome')
                    ->required(),

                Forms\Components\DatePicker::make('data')
                    ->required(),
                Forms\Components\TextInput::make('ora_entrata')
                    ->required(),
                Forms\Components\TextInput::make('coordinate_entrata_lat')
                    ->numeric(),
                Forms\Components\TextInput::make('coordinate_entrata_long')
                    ->numeric(),
                Forms\Components\TextInput::make('ora_uscita'),
                Forms\Components\TextInput::make('coordinate_uscita_lat')
                    ->numeric(),
                Forms\Components\TextInput::make('coordinate_uscita_long')
                    ->numeric(),
                Forms\Components\Toggle::make('uscita_automatica')
                    ->required(),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('anagrafica_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ora_entrata'),
                Tables\Columns\TextColumn::make('coordinate_entrata_lat')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('coordinate_entrata_long')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ora_uscita'),
                Tables\Columns\TextColumn::make('coordinate_uscita_lat')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('coordinate_uscita_long')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('uscita_automatica')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListPresenzas::route('/'),
            'create' => Pages\CreatePresenza::route('/create'),
            'edit' => Pages\EditPresenza::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
