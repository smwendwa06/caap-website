<?php
declare(strict_types=1);

// Some hosts run PHP without the mbstring extension. These small stand-ins are only used in that case,
// so the site behaves the same either way.
if (!function_exists('mb_strlen')) {
    function mb_strlen(string $s): int
    {
        return (int) preg_match_all('/./us', $s);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr(string $s, int $start, ?int $length = null): string
    {
        $chars = preg_split('//u', $s, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return implode('', array_slice($chars, $start, $length));
    }
}

/**
 * Shorten HTML or plain text down to a plain-text excerpt of roughly $length characters.
 */
function excerpt(string $html, int $length = 140): string
{
    $text = trim(strip_tags($html));
    return mb_strlen($text) > $length ? mb_substr($text, 0, $length) . '…' : $text;
}

/**
 * Turn a title into a URL-safe slug: lowercase, alphanumerics separated by single hyphens.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}
