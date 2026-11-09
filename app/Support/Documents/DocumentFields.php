<?php

namespace App\Support\Documents;

use App\Models\Workspace;
use Illuminate\Support\Str;

class DocumentFields
{
    public const LABELS = [
        'company' => 'Company name',
        'company_number' => 'Company number',
        'company_tax_id' => 'Tax ID',
        'company_address' => 'Company address',
        'company_country' => 'Country',
        'contact_name' => 'Contact name',
        'contact_first_name' => 'Contact first name',
        'contact_title' => 'Contact title',
        'contact_email' => 'Contact email',
        'workspace' => 'Workspace name',
        'workspace_url' => 'Workspace address',
        'plan' => 'Plan',
        'date' => 'Today’s date',
        'documents' => 'List of documents (email only)',
    ];

    private const PATTERN = '/\{\{\s*([a-z_]+)\s*\}\}/';

    /** @param  array<string, string|null>  $values */
    public function __construct(private array $values) {}

    /** @param  list<string>  $documents */
    public static function for(Workspace $workspace, array $documents = []): self
    {
        $workspace->loadMissing('owner');
        $contact = self::clean($workspace->company_contact_name) ?? $workspace->owner?->name;
        $address = self::clean($workspace->company_address);

        return new self([
            'company' => self::clean($workspace->company_legal_name) ?? $workspace->name,
            'company_number' => self::clean($workspace->company_number),
            'company_tax_id' => self::clean($workspace->company_tax_id),
            'company_address' => $address === null ? null : collect(preg_split('/\R/', $address) ?: [])->map(fn (string $line) => trim($line, " \t,"))->filter()->implode(', '),
            'company_country' => self::clean($workspace->company_country),
            'contact_name' => $contact,
            'contact_first_name' => $contact === null ? 'there' : Str::before(trim($contact), ' '),
            'contact_title' => self::clean($workspace->company_contact_title),
            'contact_email' => self::clean($workspace->company_contact_email) ?? $workspace->owner?->email,
            'workspace' => $workspace->name,
            'workspace_url' => $workspace->slug.'.'.config('app.domain'),
            'plan' => $workspace->effectivePlan()?->label(),
            'date' => now()->format('j F Y'),
            'documents' => $documents === [] ? null : collect($documents)->map(fn (string $name) => "- {$name}")->implode("\n"),
        ]);
    }

    public static function example(): self
    {
        return new self([
            'company' => 'Example Industries Ltd',
            'company_number' => '01234567',
            'company_tax_id' => 'GB123456789',
            'company_address' => '1 Example Street, Manchester, M1 1AA',
            'company_country' => 'United Kingdom',
            'contact_name' => 'Alex Example',
            'contact_first_name' => 'Alex',
            'contact_title' => 'Head of Infrastructure',
            'contact_email' => 'alex@example.com',
            'workspace' => 'Example Industries',
            'workspace_url' => 'example.'.config('app.domain'),
            'plan' => 'Business',
            'date' => now()->format('j F Y'),
            'documents' => "- Data processing agreement\n- Service level agreement",
        ]);
    }

    public function markdown(string $text): string
    {
        return $this->fill($text, fn (string $key, string $value) => $key === 'documents' ? $value : self::escape($value));
    }

    public function plain(string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $this->fill($text, fn (string $key, string $value) => $value)));
    }

    /** @return list<string> */
    public function missing(string ...$texts): array
    {
        return array_values(collect($texts)
            ->flatMap(fn (string $text) => preg_match_all(self::PATTERN, $text, $m) ? $m[1] : [])
            ->unique()
            ->filter(fn (string $key) => array_key_exists($key, self::LABELS) && $key !== 'documents' && blank($this->values[$key] ?? null))
            ->map(fn (string $key) => self::LABELS[$key])
            ->all());
    }

    /** @return list<string> */
    public static function unknown(string ...$texts): array
    {
        return array_values(collect($texts)
            ->flatMap(fn (string $text) => preg_match_all(self::PATTERN, $text, $m) ? $m[1] : [])
            ->unique()
            ->reject(fn (string $key) => array_key_exists($key, self::LABELS))
            ->all());
    }

    /** @param  callable(string, string): string  $format */
    private function fill(string $text, callable $format): string
    {
        return (string) preg_replace_callback(self::PATTERN, function (array $m) use ($format) {
            $key = $m[1];
            if (! array_key_exists($key, self::LABELS)) {
                return $m[0];
            }
            $value = $this->values[$key] ?? null;
            if ($key === 'documents' && $value === null) {
                return $m[0];
            }

            return $format($key, blank($value) ? '['.self::LABELS[$key].']' : (string) $value);
        }, $text);
    }

    private static function escape(string $value): string
    {
        return (string) preg_replace('/([\\\\`*_\[\]<>#|~])/', '\\\\$1', $value);
    }

    private static function clean(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
