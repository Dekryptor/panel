<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $provider
 * @property string $provider_id
 * @property string|null $email
 */
#[Fillable(['provider', 'provider_id', 'email'])]
class SocialAccount extends Model
{
    public const array PROVIDERS = ['google', 'github'];

    /** @return list<string> */
    public static function enabled(): array
    {
        return array_values(array_filter(
            self::PROVIDERS,
            fn (string $provider) => filled(config("services.{$provider}.client_id")) && filled(config("services.{$provider}.client_secret")),
        ));
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
