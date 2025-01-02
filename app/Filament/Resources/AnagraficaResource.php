<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnagraficaResource\Pages;
use App\Filament\Resources\AnagraficaResource\RelationManagers;
use App\Models\Anagrafica;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnagraficaResource extends Resource
{
    protected static ?string $model = Anagrafica::class;

    protected static ?string $slug = 'anagrafiche';
    protected static ?string $label = 'Anagrafiche';
    protected static ?string $navigationLabel = 'Anagrafiche';
    protected static ?string $pluralLabel = 'Anagrafiche';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User Email')
                    ->relationship('user', 'email')
                    ->required(),

                Forms\Components\Select::make('sede_id')
                    ->relationship('sede', 'nome')
                    ->required(),
                Forms\Components\TextInput::make('nome')
                    ->required(),
                Forms\Components\TextInput::make('cognome')
                    ->required(),
                Forms\Components\Toggle::make('attivo')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sede.nome')
                    ->sortable(),


                Tables\Columns\IconColumn::make('attivo')
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
            'index' => Pages\ListAnagraficas::route('/'),
            'create' => Pages\CreateAnagrafica::route('/create'),
            'edit' => Pages\EditAnagrafica::route('/{record}/edit'),
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
