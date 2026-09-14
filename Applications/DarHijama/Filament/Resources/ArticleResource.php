<?php

namespace Applications\DarHijama\Filament\Resources;

use Applications\DarHijama\Application\Services\Seo\ArticleSeoResolver;
use Applications\DarHijama\Domain\Article;
use Applications\DarHijama\Domain\ArticleCategory;
use Applications\DarHijama\Domain\ArticleStatus;
use Applications\DarHijama\Domain\ArticleTag;
use Applications\DarHijama\Filament\Resources\ArticleResource\Pages;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Mythos\Core\Identity\Models\User;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';

    protected static ?string $navigationGroup = 'Dar Hijama';

    protected static ?string $navigationLabel = 'المقالات';

    protected static ?string $modelLabel = 'مقال';

    protected static ?string $pluralModelLabel = 'المقالات';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('article')->columnSpanFull()->tabs([
                Tab::make('المحتوى')
                    ->icon('heroicon-o-document-text')
                    ->schema(self::contentFields()),
                Tab::make('النشر')
                    ->icon('heroicon-o-calendar')
                    ->schema(self::publishingFields()),
                Tab::make('SEO')
                    ->icon('heroicon-o-magnifying-glass')
                    ->schema(self::seoFields()),
                Tab::make('SEO متقدم')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema(self::advancedSeoFields()),
            ]),
        ]);
    }

    /** @return array<int, \Filament\Forms\Components\Component> */
    private static function contentFields(): array
    {
        return [
            TextInput::make('title')
                ->label('العنوان')
                ->required()
                ->maxLength(200)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $state, Set $set, Get $get, ?string $old): void {
                    // Only auto-fill the slug while the editor hasn't touched
                    // it themselves — never overwrite a manually-set slug.
                    if (blank($get('slug')) || $get('slug') === Str::slug((string) $old)) {
                        $set('slug', Str::slug($state));
                    }
                }),
            TextInput::make('slug')
                ->label('الرابط (slug)')
                ->required()
                ->maxLength(220)
                ->unique(ignoreRecord: true)
                ->helperText('يُولَّد تلقائيًا من العنوان (بترميز لاتيني مستقر) — يمكن تعديله يدويًا.')
                ->rule('regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'),
            Textarea::make('excerpt')
                ->label('ملخص قصير')
                ->maxLength(320)
                ->rows(2)
                ->helperText('يُستخدم كبديل تلقائي لوصف SEO إن تُرك حقل "Meta description" فارغًا.'),
            RichEditor::make('content')
                ->label('المحتوى')
                ->required()
                ->toolbarButtons([
                    'h2', 'h3', 'bold', 'italic', 'underline',
                    'bulletList', 'orderedList', 'link', 'blockquote',
                    'attachFiles', 'undo', 'redo',
                ])
                ->columnSpanFull(),
            FileUpload::make('featured_image')
                ->label('الصورة الرئيسية')
                ->image()
                ->disk('public')
                ->directory('dar-hijama/articles')
                ->imagePreviewHeight('160')
                ->maxSize(4096),
            TextInput::make('featured_image_alt')
                ->label('النص البديل للصورة (alt)')
                ->maxLength(160)
                ->helperText('صِف الصورة فعليًا — بدون حشو كلمات مفتاحية.'),
            Select::make('author_id')
                ->label('الكاتب')
                ->relationship('author', 'name')
                ->options(fn () => User::query()->role(['dar-hijama-admin', 'dar-hijama-manager'])->pluck('name', 'id'))
                ->default(fn () => auth()->id())
                ->searchable(),
            Select::make('category_id')
                ->label('التصنيف')
                ->relationship('category', 'name')
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')->label('الاسم')->required()->live(onBlur: true)
                        ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')->label('الرابط')->required(),
                ]),
            Select::make('tags')
                ->label('الوسوم (Tags)')
                ->relationship('tags', 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')->label('الاسم')->required()->live(onBlur: true)
                        ->afterStateUpdated(fn (string $state, Set $set) => $set('slug', Str::slug($state))),
                    TextInput::make('slug')->label('الرابط')->required(),
                ]),
        ];
    }

    /** @return array<int, \Filament\Forms\Components\Component> */
    private static function publishingFields(): array
    {
        return [
            Select::make('status')
                ->label('الحالة')
                ->options(collect(ArticleStatus::cases())->mapWithKeys(
                    fn (ArticleStatus $s) => [$s->value => $s->label()],
                ))
                ->default(ArticleStatus::Draft->value)
                ->required()
                ->live(),
            DateTimePicker::make('published_at')
                ->label('تاريخ النشر')
                ->native(false)
                ->default(now())
                ->visible(fn (Get $get) => $get('status') === ArticleStatus::Published->value)
                ->required(fn (Get $get) => $get('status') === ArticleStatus::Published->value),
            DateTimePicker::make('scheduled_at')
                ->label('موعد الجدولة')
                ->native(false)
                ->minDate(now())
                ->visible(fn (Get $get) => $get('status') === ArticleStatus::Scheduled->value)
                ->required(fn (Get $get) => $get('status') === ArticleStatus::Scheduled->value)
                ->helperText('يحوّل المقال تلقائيًا إلى "منشور" عند هذا الموعد (عبر Scheduler).'),
        ];
    }

    /** @return array<int, \Filament\Forms\Components\Component> */
    private static function seoFields(): array
    {
        return [
            Section::make('حقول SEO')->columns(2)->schema([
                TextInput::make('seo_title')
                    ->label('SEO Title')
                    ->maxLength(70)
                    ->live(onBlur: true)
                    ->helperText('إن تُرك فارغًا: العنوان + "| دار الحجامة".'),
                TextInput::make('focus_keyword')
                    ->label('الكلمة المفتاحية الأساسية'),
                Textarea::make('meta_description')
                    ->label('Meta description')
                    ->maxLength(320)
                    ->rows(2)
                    ->live(onBlur: true)
                    ->columnSpanFull()
                    ->helperText('إن تُرك فارغًا: يُستخرج من الملخص أو من بداية المحتوى.'),
                TextInput::make('secondary_keywords')
                    ->label('كلمات مفتاحية ثانوية')
                    ->helperText('مفصولة بفواصل.')
                    ->columnSpanFull(),
                TextInput::make('canonical_url')
                    ->label('Canonical URL')
                    ->url()
                    ->helperText('اتركه فارغًا لاستخدام رابط المقال نفسه (الحالة الافتراضية والصحيحة في أغلب الأحيان).'),
            ]),
            Section::make('معاينة Google')->schema([
                Placeholder::make('google_preview')
                    ->label('')
                    ->content(fn (Get $get) => self::googlePreview($get)),
            ]),
            Section::make('معاينة المشاركة (Social)')->columns(2)->schema([
                TextInput::make('social_title')
                    ->label('Social title')
                    ->helperText('إن تُرك فارغًا: يُستخدم SEO Title.'),
                Textarea::make('social_description')
                    ->label('Social description')
                    ->rows(2)
                    ->helperText('إن تُرك فارغًا: يُستخدم Meta description.'),
                FileUpload::make('og_image')
                    ->label('OG Image')
                    ->image()
                    ->disk('public')
                    ->directory('dar-hijama/articles/og')
                    ->helperText('إن تُرك فارغًا: تُستخدم الصورة الرئيسية.')
                    ->columnSpanFull(),
            ]),
        ];
    }

    /** @return array<int, \Filament\Forms\Components\Component> */
    private static function advancedSeoFields(): array
    {
        return [
            Toggle::make('noindex')
                ->label('Noindex')
                ->helperText('منع محركات البحث من فهرسة هذا المقال.'),
            Toggle::make('nofollow')
                ->label('Nofollow')
                ->helperText('منع اتباع الروابط داخل هذا المقال.'),
            Select::make('schema_type')
                ->label('نوع Schema.org')
                ->options(['Article' => 'Article', 'BlogPosting' => 'BlogPosting'])
                ->default('BlogPosting')
                ->required(),
            TextInput::make('breadcrumb_label')
                ->label('نص Breadcrumb')
                ->maxLength(100)
                ->helperText('إن تُرك فارغًا: يُستخدم العنوان.'),
            Section::make('SEO Diagnostics')->schema([
                Placeholder::make('seo_diagnostics')
                    ->label('')
                    ->content(fn (Get $get) => self::diagnosticsPreview($get)),
            ]),
        ];
    }

    private static function transientArticle(Get $get): Article
    {
        $article = new Article([
            'title' => (string) $get('title'),
            'slug' => (string) $get('slug'),
            'excerpt' => $get('excerpt'),
            'content' => (string) $get('content'),
            'featured_image' => $get('featured_image'),
            'featured_image_alt' => $get('featured_image_alt'),
            'seo_title' => $get('seo_title'),
            'meta_description' => $get('meta_description'),
            'canonical_url' => $get('canonical_url'),
            'og_image' => $get('og_image'),
            'social_title' => $get('social_title'),
            'social_description' => $get('social_description'),
            'noindex' => (bool) $get('noindex'),
            'nofollow' => (bool) $get('nofollow'),
        ]);

        // ignore the current record's id in the duplicate-slug diagnostic
        $article->exists = false;

        return $article;
    }

    private static function googlePreview(Get $get): \Illuminate\Support\HtmlString
    {
        $resolver = app(ArticleSeoResolver::class);
        $article = self::transientArticle($get);

        $title = e($resolver->seoTitle($article)->value ?? '(بدون عنوان)');
        $url = e($resolver->canonicalUrl($article)->value ?? config('app.url').'/articles/…');
        $description = e($resolver->metaDescription($article)->value ?? '(بدون وصف)');

        return new \Illuminate\Support\HtmlString(<<<HTML
            <div style="font-family:arial,sans-serif;max-width:560px;direction:ltr;text-align:left">
                <div style="color:#1a0dab;font-size:18px;line-height:1.3">{$title}</div>
                <div style="color:#006621;font-size:13px">{$url}</div>
                <div style="color:#545454;font-size:13px;line-height:1.4">{$description}</div>
            </div>
        HTML);
    }

    private static function diagnosticsPreview(Get $get): \Illuminate\Support\HtmlString
    {
        $resolver = app(ArticleSeoResolver::class);
        $diagnostics = $resolver->diagnostics(self::transientArticle($get));

        $labels = [
            'missing' => '⚠️ غير موجود', 'too_short' => '⚠️ قصير جدًا', 'too_long' => '⚠️ طويل جدًا',
            'short' => '⚠️ قصير', 'long' => '⚠️ طويل', 'acceptable' => '✅ مناسب',
            'valid' => '✅ صالح', 'invalid' => '⚠️ غير صالح', 'duplicate' => '⚠️ مكرر',
        ];

        $rows = [
            sprintf('العنوان (%d حرفًا): %s', $diagnostics['title']['length'], $labels[$diagnostics['title']['state']]),
            sprintf('الوصف (%d حرفًا): %s', $diagnostics['description']['length'], $labels[$diagnostics['description']['state']]),
            sprintf('الرابط (slug): %s', $labels[$diagnostics['slug']['state']]),
            sprintf('Canonical: %s', $labels[$diagnostics['canonical']['state']]),
            sprintf(
                'الصورة الرئيسية: %s — النص البديل: %s',
                $diagnostics['featured_image']['exists'] ? '✅ موجودة' : '⚠️ غير موجودة',
                $diagnostics['featured_image']['has_alt'] ? '✅ موجود' : '⚠️ غير موجود',
            ),
            sprintf(
                'العناوين: H2×%d، H3×%d — الروابط: داخلية×%d، خارجية×%d — الصور: %d (بدون alt: %d)',
                $diagnostics['content']['h2_count'],
                $diagnostics['content']['h3_count'],
                $diagnostics['content']['internal_links'],
                $diagnostics['content']['external_links'],
                $diagnostics['content']['images'],
                $diagnostics['content']['images_missing_alt'],
            ),
            sprintf(
                'الفهرسة: %s — اتباع الروابط: %s',
                $diagnostics['indexability']['index'] ? '✅ index' : '⛔ noindex',
                $diagnostics['indexability']['follow'] ? '✅ follow' : '⛔ nofollow',
            ),
        ];

        $items = implode('', array_map(fn (string $r) => '<li style="margin-bottom:4px">'.e($r).'</li>', $rows));

        return new \Illuminate\Support\HtmlString(
            '<ul style="list-style:none;padding:0;font-size:13px">'.$items.'</ul>'
        );
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('العنوان')->searchable()->limit(50),
            TextColumn::make('category.name')->label('التصنيف')->badge(),
            TextColumn::make('status')->label('الحالة')
                ->badge()
                ->formatStateUsing(fn (ArticleStatus $state) => $state->label())
                ->color(fn (ArticleStatus $state) => $state->color()),
            TextColumn::make('published_at')->label('تاريخ النشر')->dateTime('Y-m-d H:i')->sortable(),
            IconColumn::make('seo_title')->label('SEO')->boolean()
                ->trueIcon('heroicon-o-check-circle')->falseIcon('heroicon-o-exclamation-triangle')
                ->getStateUsing(fn (Article $r) => filled($r->seo_title) && filled($r->meta_description)),
            IconColumn::make('featured_image')->label('صورة')->boolean(),
            TextColumn::make('author.name')->label('الكاتب'),
        ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->label('الحالة')
                    ->options(collect(ArticleStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                SelectFilter::make('category_id')->label('التصنيف')->relationship('category', 'name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
