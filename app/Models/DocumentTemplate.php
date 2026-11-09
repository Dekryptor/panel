<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $kind
 * @property string|null $description
 * @property string $title
 * @property string $body
 * @property string $email_subject
 * @property string $email_body
 * @property int|null $updated_by_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User|null $updatedBy
 */
class DocumentTemplate extends Model
{
    public const KINDS = [
        'dpa' => 'Data processing',
        'sla' => 'Service level',
        'security' => 'Security and compliance',
        'other' => 'Other',
    ];

    protected $guarded = ['id'];

    /** @return BelongsTo<User, $this> */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
