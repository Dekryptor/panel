<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $doc_page_id
 * @property string $locale
 * @property string $title
 * @property string|null $description
 * @property string $body
 * @property string $source_hash
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['doc_page_id', 'locale', 'title', 'description', 'body', 'source_hash'])]
class DocPageTranslation extends Model
{
    /** @return BelongsTo<DocPage, $this> */
    public function page(): BelongsTo
    {
        return $this->belongsTo(DocPage::class, 'doc_page_id');
    }
}
