<?php

namespace Applications\DarHijama\Filament\Resources;

use Applications\DarHijama\Domain\ArticleTag;
use Applications\DarHijama\Filament\Resources\ArticleTagResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArticleTagResource extends Resource
{
    protected static ?string $model = ArticleTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Dar Hijama';

    protected static ?string $navigationLabel = 'وسوم المقالات';

    protected static ?string $modelLabel = 'وسم';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('الاسم')->required()->maxLength(60)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $state, Set $set, Get $get, ?string $old): void {
                    if (blank($get('slug')) || $get('slug') === Str::slug((string) $old)) {
                        $set('slug', Str::slug($state));
                    }
                }),
            TextInput::make('slug')->label('الرابط')->required()->maxLength(80)->unique(ignoreRecord: true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('الاسم')->searchable(),
            TextColumn::make('slug')->label('الرابط'),
            TextColumn::make('articles_count')->label('عدد المقالات')->counts('articles'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticleTags::route('/'),
            'create' => Pages\CreateArticleTag::route('/create'),
            'edit' => Pages\EditArticleTag::route('/{record}/edit'),
        ];
    }
}
