<?php

namespace App\Filament\Resources;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Symfony\Contracts\Service\Attribute\Required;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-s-tag';


    //navegacion agrupacion
    protected static ?string $navigationGroup = 'Previa';

    //traduccion
    protected static ?string $modelLabel = 'Expediente';
    protected static ?string $pluralLabel = 'Expedientes';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make()
                    ->schema([
                    //Formulario
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->live(onBlur: true)
                        ->unique()
                        ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }
                            $set('slug', Str::slug($state));
                        }),
                    Forms\Components\TextInput::make('slug')
                    ->translateLabel()
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(Product::class, 'slug', ignoreRecord: true),
                    Forms\Components\MarkdownEditor::make('description')->label(__('Descripción'))
                    ->columnSpan('full')
                ])->columns(2),
                Forms\Components\Section::make('Cotizacion y Disponibilidad')
                    ->schema([
                //Formulario
                Forms\Components\TextInput::make('sku')
                    ->label("SKU (Unidad de Mantenimiento de Stock)")
                    ->unique()
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label(__('Precio de la cotización'))
                    ->numeric()
                    ->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')
                    ->required(),
                Forms\Components\TextInput::make('quantity')->label(__('Cantidad'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(10000)
                    ->required(),
                Forms\Components\Select::make('type')->label(__('Tipo'))
                    ->required()
                    ->options([
                        'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                        'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                    ]),
            ])->columns(2)

            ]),
            Forms\Components\Group::make()
            ->schema([
                Forms\Components\Section::make('Estado del Expediente')
                ->schema([
                //Formulario
                Forms\Components\Toggle::make('is_visible')
                    ->label(__('Visibilidad'))
                    ->helperText('Habilitar o deshabillitar la visibilidad')
                    ->default(true),
                Forms\Components\Toggle::make('is_featured')->label(__('Procesando'))
                ->helperText('Tramite con prioridad de IMPORTANTE')
                ->default(true),
                Forms\Components\DatePicker::make('published_at')
                    ->default(now())
                    ->minDate(Carbon::now()->startOfDay())
                    ->label(__('Fecha de Publicación')),
                ])->columns(2),
                Forms\Components\Section::make('Documentos del Expediente')
                ->schema([
                //Formulario
                Forms\Components\FileUpload::make('image')->label(__('Imagen'))
                        ->required(),
                ])->collapsible(),
                Forms\Components\Section::make('Asociaciones')
                ->schema([
                //Formulario
                    Forms\Components\Select::make('brand_id')
                        ->label(__('Marca'))
                        ->relationship('brand', 'name'),
                ]),

            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //Columnas de la tabla
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('brand.name'),
                Tables\Columns\IconColumn::make('is_visible')->boolean(),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('quantity'),
                Tables\Columns\TextColumn::make('published_at'),
                Tables\Columns\TextColumn::make('type'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}


