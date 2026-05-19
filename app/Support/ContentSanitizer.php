<?php

namespace App\Support;

use Illuminate\Support\Str;

class ContentSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><blockquote><span>';

    public function html(?string $html): string
    {
        $html = trim((string) $html);
        $html = preg_replace('/<\s*(script|style)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html) ?? '';
        $html = strip_tags($html, self::ALLOWED_TAGS);
        $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $html) ?? '';
        $html = preg_replace("/\s+on\w+\s*=\s*'[^']*'/i", '', $html) ?? '';
        $html = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/i', '', $html) ?? '';
        $html = preg_replace('/(href\s*=\s*["\'])\s*javascript:[^"\']*(["\'])/i', '$1#$2', $html) ?? '';

        return trim($html);
    }

    public function plain(?string $text, string $fallback, int $limit = 160): string
    {
        $raw = (string) $text;
        $raw = preg_replace('/<\s*(script|style)[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $raw) ?? '';

        $value = html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';
        $value = trim($value);

        if ($value === '' || Str::contains(Str::lower($value), ['<script', 'javascript:'])) {
            $value = $fallback;
        }

        return Str::limit($value, $limit, '');
    }

    public function title(?string $text, string $fallback = 'Konten', int $limit = 500): string
    {
        return $this->plain($text, $fallback, $limit) ?: $fallback;
    }
}
