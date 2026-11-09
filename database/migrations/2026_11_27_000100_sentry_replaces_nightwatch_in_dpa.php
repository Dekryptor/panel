<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('document_templates')->where('kind', 'dpa')->get(['id', 'body']) as $template) {
            $body = (string) preg_replace(
                '/^\| Laravel Nightwatch .*$/m',
                '| Sentry             | Error and performance monitoring for the panel and the daemon, scrubbed of secrets, email addresses, hostnames and IP addresses | European Union (Germany) |',
                (string) $template->body,
            );

            if ($body !== $template->body) {
                DB::table('document_templates')->where('id', $template->id)->update(['body' => $body, 'updated_at' => now()]);
            }
        }
    }
};
