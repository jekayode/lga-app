<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Request-scoped page metadata. Pages set what they know; the shared head partial renders it with sensible defaults.
 */
class Seo
{
    public const int DESCRIPTION_LENGTH = 160;

    protected ?string $title = null;

    protected bool $appendSiteName = true;

    protected ?string $description = null;

    protected ?string $image = null;

    protected ?string $imageAlt = null;

    protected string $type = 'website';

    protected ?string $canonical = null;

    protected ?string $robots = null;

    /** @var array<string, string> */
    protected array $openGraphExtras = [];

    /** @var list<array<string, mixed>> */
    protected array $schemas = [];

    public function title(string $title, bool $appendSiteName = true): static
    {
        $this->title = $title;
        $this->appendSiteName = $appendSiteName;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = filled($description) ? static::plainText($description) : null;

        return $this;
    }

    public function image(?string $url, ?string $alt = null): static
    {
        $this->image = $url;
        $this->imageAlt = $alt;

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function canonical(string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function indexable(): static
    {
        $this->robots = 'index, follow, max-image-preview:large';

        return $this;
    }

    public function noindex(): static
    {
        $this->robots = 'noindex, nofollow';

        return $this;
    }

    public function openGraph(string $property, string $content): static
    {
        $this->openGraphExtras[$property] = $content;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    public function schema(array $schema): static
    {
        $this->schemas[] = $schema;

        return $this;
    }

    public function documentTitle(?string $layoutTitle = null): string
    {
        $siteName = (string) config('app.name');
        $title = $this->title ?? $layoutTitle;

        if (blank($title)) {
            return $siteName.' — '.config('seo.tagline');
        }

        return $this->title !== null && ! $this->appendSiteName
            ? $title
            : $title.' | '.$siteName;
    }

    public function resolvedDescription(): string
    {
        return $this->description ?? (string) config('seo.description');
    }

    public function resolvedImage(): string
    {
        return $this->image ?? Media::heroUrl();
    }

    public function resolvedImageAlt(): string
    {
        return $this->imageAlt ?? (string) config('app.name');
    }

    public function resolvedCanonical(): string
    {
        return $this->canonical ?? url()->current();
    }

    public function resolvedRobots(bool $noindexByDefault = false): string
    {
        return $this->robots ?? ($noindexByDefault ? 'noindex, nofollow' : 'index, follow, max-image-preview:large');
    }

    public function resolvedType(): string
    {
        return $this->type;
    }

    /**
     * @return array<string, string>
     */
    public function openGraphExtras(): array
    {
        return $this->openGraphExtras;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function schemas(): array
    {
        return $this->schemas;
    }

    /**
     * The page's structured data as a single schema.org graph, safe to embed in a script tag.
     */
    public function jsonLd(): string
    {
        return (string) json_encode(
            ['@context' => 'https://schema.org', '@graph' => $this->schemas],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function organizationSchema(): array
    {
        return array_filter([
            '@type' => 'Organization',
            '@id' => url('/').'/#organization',
            'name' => config('app.name'),
            'alternateName' => 'LGA',
            'url' => url('/'),
            'logo' => Media::logoUrl(),
            'slogan' => config('seo.tagline'),
            'description' => config('seo.description'),
            'email' => config('mail.from.address'),
            'areaServed' => [
                '@type' => 'State',
                'name' => 'Lagos State',
                'containedInPlace' => ['@type' => 'Country', 'name' => 'Nigeria'],
            ],
            'sameAs' => config('seo.same_as') ?: null,
        ]);
    }

    public static function plainText(string $html, int $limit = self::DESCRIPTION_LENGTH): string
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return Str::limit((string) Str::of($text)->squish(), $limit, '…', preserveWords: true);
    }
}
