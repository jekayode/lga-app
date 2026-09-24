<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Category;
use App\Support\Media;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        $mediaDisk = Media::diskName();

        return $schema
            ->components([
                Section::make('Story')
                    ->description('Published posts appear on the homepage and news page. Media uses the MEDIA_DISK (local public or Cloudflare R2).')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Used in the public URL: /news/your-slug'),
                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (?string $state, callable $set) => $set('slug', Str::slug($state ?? ''))),
                                TextInput::make('slug')->required(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                return Category::query()->create([
                                    ...$data,
                                    'is_active' => true,
                                ])->getKey();
                            })
                            ->helperText('Shown as the green badge on news cards.'),
                        Select::make('author_id')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->id())
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Publish at')
                            ->helperText('Leave empty to keep as draft. Set to now (or earlier) to publish.'),
                        FileUpload::make('featured_image_path')
                            ->label('Featured image')
                            ->image()
                            ->directory('posts/featured')
                            ->disk($mediaDisk)
                            ->visibility($mediaDisk === 'public' ? 'public' : 'private')
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:10',
                                '16:9',
                                '4:3',
                                null,
                            ])
                            ->maxSize(5120)
                            ->helperText('Used on homepage and news cards. Prefer WebP under ~150KB.')
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Gallery')
                    ->description('Optional event photos. Upload WebP/AVIF when possible; keep each under ~250KB.')
                    ->schema([
                        Repeater::make('images')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(0)
                            ->schema([
                                FileUpload::make('path')
                                    ->label('Image')
                                    ->image()
                                    ->directory('posts/gallery')
                                    ->disk($mediaDisk)
                                    ->visibility($mediaDisk === 'public' ? 'public' : 'private')
                                    ->required()
                                    ->maxSize(5120),
                                TextInput::make('alt_text')
                                    ->label('Alt text')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
