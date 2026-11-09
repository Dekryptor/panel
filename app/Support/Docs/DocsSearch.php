<?php

namespace App\Support\Docs;

use App\Models\DocPage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DocsSearch
{
    private const STOP = ['the', 'and', 'for', 'how', 'what', 'can', 'does', 'with', 'you', 'your', 'this', 'that', 'are', 'from', 'why', 'when', 'where', 'which', 'who', 'have', 'has', 'not', 'but', 'get', 'its', 'into', 'out', 'any', 'all', 'our', 'about', 'there', 'they', 'them', 'will', 'just', 'need', 'want', 'make', 'use', 'doesn', 'isn', 'don', 'cant', 'cannot', 'please', 'help', 'serveros'];

    /** @return Collection<int, DocPage> */
    public function pages(): Collection
    {
        return DocPage::query()->with('section')->where('published', true)->orderBy('id')->get();
    }

    /** @return Collection<int, DocPage> */
    public function search(string $question, int $limit = 4): Collection
    {
        $words = $this->words($question);
        if ($words === []) {
            return collect();
        }

        return $this->pages()
            ->map(function (DocPage $page) use ($words) {
                $title = $this->words($page->title);
                $description = $this->words((string) $page->description);
                $body = array_count_values($this->words($page->body));

                $score = 0;
                foreach ($words as $word) {
                    $score += in_array($word, $title, true) ? 6 : 0;
                    $score += in_array($word, $description, true) ? 3 : 0;
                    $score += min($body[$word] ?? 0, 5);
                }

                return [$page, $score];
            })
            ->filter(fn (array $match) => $match[1] > 0)
            ->sortByDesc(fn (array $match) => $match[1])
            ->take($limit)
            ->map(fn (array $match) => $match[0])
            ->values();
    }

    /** @return list<array{title: string, description: ?string, section: string, group: string, href: string, snippet: ?string}> */
    public function find(string $query, int $limit = 8): array
    {
        preg_match_all('/[a-z0-9]+/', strtolower($query), $matches);
        $terms = array_values(array_unique($matches[0]));
        if ($terms === []) {
            return [];
        }

        $results = [];
        foreach ($this->pages() as $page) {
            $title = $this->tokens($page->title);
            $description = $this->tokens((string) $page->description);
            $text = self::plain($page->body);
            $body = $this->tokens($text);

            $score = 0;
            foreach ($terms as $term) {
                $starts = fn (string $word) => str_starts_with($word, $term);
                $inTitle = count(array_filter($title, $starts));
                $inDescription = count(array_filter($description, $starts));
                $inBody = count(array_filter($body, $starts));
                if ($inTitle + $inDescription + $inBody === 0) {
                    continue 2;
                }
                $score += ($inTitle ? 10 : 0) + ($inDescription ? 4 : 0) + min($inBody, 6);
                $score += (in_array($term, $title, true) ? 6 : 0) + (in_array($term, $description, true) ? 2 : 0);
            }

            $results[] = [$score, [
                'title' => $page->title,
                'description' => $page->description,
                'section' => $page->section->title,
                'group' => $page->group,
                'href' => self::url($page),
                'snippet' => $this->snippet($text, $terms),
            ]];
        }

        usort($results, fn (array $a, array $b) => $b[0] <=> $a[0]);

        return array_map(fn (array $result) => $result[1], array_slice($results, 0, $limit));
    }

    public static function plain(string $markdown): string
    {
        $text = preg_replace([
            '/```.*?```/s',
            '/!\[[^\]]*\]\([^)]*\)/',
            '/\[([^\]]+)\]\([^)]*\)/',
            '/^\s{0,3}(#{1,6}|>|[-*+]|\d+\.)\s+/m',
            '/[*_`~|]/',
        ], ['', '', '$1', '', ''], $markdown);

        return trim(preg_replace('/\s+/', ' ', (string) $text));
    }

    /** @param  list<string>  $terms */
    private function snippet(string $text, array $terms): ?string
    {
        $at = null;
        foreach ($terms as $term) {
            if (preg_match('/\b'.preg_quote($term, '/').'/i', $text, $match, PREG_OFFSET_CAPTURE)) {
                $at = $at === null ? $match[0][1] : min($at, $match[0][1]);
            }
        }
        if ($at === null) {
            return null;
        }

        $start = max(0, $at - 50);
        if ($start > 0 && ($space = strpos($text, ' ', $start)) !== false && $space < $at) {
            $start = $space + 1;
        }

        return ($start > 0 ? '…' : '').Str::limit(substr($text, $start), 150);
    }

    /** @return list<string> */
    private function tokens(string $text): array
    {
        preg_match_all('/[a-z0-9]+/', strtolower($text), $matches);

        return $matches[0];
    }

    public static function url(DocPage $page): string
    {
        return DocsUrl::page($page->section->slug, $page->slug);
    }

    /** @return list<string> */
    private function words(string $text): array
    {
        preg_match_all('/[a-z0-9]{3,}/', strtolower($text), $matches);

        return array_values(array_map(
            fn (string $word) => strlen($word) > 4 ? preg_replace('/(es|s)$/', '', $word) : $word,
            array_diff($matches[0], self::STOP),
        ));
    }
}
