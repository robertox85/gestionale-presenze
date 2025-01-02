<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FestivitaResource\Pages;
use App\Filament\Resources\FestivitaResource\RelationManagers;
use App\Models\Festivita;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FestivitaResource extends Resource
{

    protected static ?string $model = Festivita::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $slug = 'festivita';
    protected static ?string $label = 'Festivita';
    protected static ?string $navigationLabel = 'Festivita';
    protected static ?string $pluralLabel = 'Festivita';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([


                Forms\Components\DatePicker::make('data_festivita')
                    ->required(),

                Forms\Components\TextInput::make('descrizione')
                    ->required(),

                Forms\Components\TextInput::make('country_code')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country_code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_festivita')
                    ->searchable()
                    ->dateTime(
                        function (Festivita $model) {
                            return $model->data_festivita->format('Y-m-d');
                        }
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('descrizione')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListFestivitas::route('/'),
            'create' => Pages\CreateFestivita::route('/create'),
            'edit' => Pages\EditFestivita::route('/{record}/edit'),
        ];
    }
}
