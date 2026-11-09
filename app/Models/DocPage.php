<?php

namespace App\Models;

use App\Support\Docs\DocsMarkdown;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property int $doc_section_id
 * @property string $group
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property string $body
 * @property int $position
 * @property bool $published
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['doc_section_id', 'group', 'title', 'slug', 'description', 'body', 'position', 'published'])]
class DocPage extends Model
{
    protected function casts(): array
    {
        return ['published' => 'boolean'];
    }

    /** @return BelongsTo<DocSection, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(DocSection::class, 'doc_section_id');
    }

    /** @return HasMany<DocPageTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(DocPageTranslation::class);
    }

    public function sourceHash(): string
    {
        return hash('sha256', implode("\0", [$this->title, (string) $this->description, $this->body]));
    }

    public function localized(): self
    {
        $locale = App::getLocale();
        if ($locale === 'en') {
            return $this;
        }

        $translation = $this->relationLoaded('translations')
            ? $this->translations->firstWhere('locale', $locale)
            : $this->translations()->where('locale', $locale)->first();

        if ($translation === null || $translation->source_hash !== $this->sourceHash()) {
            return $this;
        }

        $page = clone $this;
        $page->title = $translation->title;
        $page->description = $translation->description;
        $page->body = $translation->body;

        return $page;
    }

    public function html(): string
    {
        return DocsMarkdown::render($this->body);
    }
}
