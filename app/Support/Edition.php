<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class Edition
{
    public static function selfHosted(): bool
    {
        return config('serveros.edition') === 'self-hosted';
    }

    public static function home(Request $request): RedirectResponse
    {
        return match (true) {
            $request->user() !== null => redirect()->route('dashboard'),
            User::query()->doesntExist() => redirect()->route('register'),
            default => redirect()->route('login'),
        };
    }
}
