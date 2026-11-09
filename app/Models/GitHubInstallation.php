<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $workspace_id
 * @property int $installation_id
 * @property string $account_login
 * @property string|null $account_type
 * @property int|null $connected_by
 */
#[Fillable(['installation_id', 'account_login', 'account_type', 'connected_by'])]
class GitHubInstallation extends Model
{
    protected $table = 'github_installations';

    protected function casts(): array
    {
        return ['installation_id' => 'integer'];
    }

    /** @return BelongsTo<Workspace, $this> */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
