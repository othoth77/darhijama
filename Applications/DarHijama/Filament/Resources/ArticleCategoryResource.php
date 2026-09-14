<?php

namespace Applications\DarHijama\Filament\Resources;

use Applications\DarHijama\Domain\ArticleCategory;
use Applications\DarHijama\Filament\Resources\ArticleCategoryResource\Pages;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ArticleCategoryResource extends Resource
{
    protected static ?string $model = ArticleCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Dar Hijama';

    protected static ?string $navigationLabel = 'تصنيفات المقالات';

    protected static ?string $modelLabel = 'تصنيف';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->label('الاسم')->required()->maxLength(100)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $state, Set $set, Get $get, ?string $old): void {
                    if (blank($get('slug')) || $get('slug') === Str::slug((string) $old)) {
                        $set('slug', Str::slug($state));
                    }
                }),
            TextInput::make('slug')->label('الرابط')->required()->maxLength(120)->unique(ignoreRecord: true),
            Textarea::make('description')->label('وصف مختصر')->rows(2)->columnSpanFull(),
            TextInput::make('meta_title')->label('SEO Title')->maxLength(70),
            Textarea::make('meta_description')->label('Meta description')->rows(2)->columnSpanFull(),
        ])->columns(2);
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
            'index' => Pages\ListArticleCategories::route('/'),
            'create' => Pages\CreateArticleCategory::route('/create'),
            'edit' => Pages\EditArticleCategory::route('/{record}/edit'),
        ];
    }
}
