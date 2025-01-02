<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SedeResource\Pages;
use App\Filament\Resources\SedeResource\RelationManagers;
use App\Models\Sede;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SedeResource extends Resource
{
    protected static ?string $model = Sede::class;


    protected static ?string $slug = 'sedi';
    protected static ?string $label = 'Sede';
    protected static ?string $navigationLabel = 'Sede';
    protected static ?string $pluralLabel = 'Sedi';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\TextInput::make('nome')
                    ->required(),

                Forms\Components\TextInput::make('indirizzo'),

                Forms\Components\TextInput::make('latitudine')
                    ->numeric(),
                Forms\Components\TextInput::make('longitudine')
                    ->numeric(),

                Forms\Components\Select::make('fuso_orario')
                    ->options(array_combine(timezone_identifiers_list(), timezone_identifiers_list()))
                    ->required()
                    ->searchable(),

                Forms\Components\CheckboxList::make('giorni_feriali')
                    ->options([
                        1 => 'Lunedì',
                        2 => 'Martedì',
                        3 => 'Mercoledì',
                        4 => 'Giovedì',
                        5 => 'Venerdì',
                        6 => 'Sabato',
                        7 => 'Domenica',
                    ])
                    ->default([1, 2, 3, 4, 5]) // Default: lunedì-venerdì
                    ->columnSpanFull()
                    ->label('Giorni lavorativi'),

                Forms\Components\Toggle::make('esclusione_festivi')
                    ->label('Considera festività come non lavorative')
                    ->hint('Se attivo, le festività non saranno considerate come giorni lavorativi')
                    ->columnSpanFull()
                    ->default(true),

                Forms\Components\TimePicker::make('orario_inizio')
                    ->required(),
                Forms\Components\TimePicker::make('orario_fine')
                    ->required(),

                Forms\Components\TextInput::make('country_code'),

                Forms\Components\Toggle::make('attiva')
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // country_code
                // data_festivita


                Tables\Columns\TextColumn::make('nome')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('indirizzo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitudine')
                    ->numeric()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitudine')
                    ->numeric()
                    ->toggleable()
                    ->toggledHiddenByDefault()
                    ->sortable(),

                Tables\Columns\TextColumn::make('orario_inizio')->toggleable(),
                Tables\Columns\TextColumn::make('orario_fine')->toggleable(),
                Tables\Columns\TextColumn::make('fuso_orario')
                    ->toggleable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('country_code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('attiva')
                    ->toggleable()
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSedes::route('/'),
            'create' => Pages\CreateSede::route('/create'),
            'edit' => Pages\EditSede::route('/{record}/edit'),
        ];
    }
}
