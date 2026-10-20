<?php
// includes/validate.php

// Returns a clean http(s) URL, '' if empty, or null if invalid
function clean_url(string $url): ?string {
    $url = trim($url);
    if ($url === '') return '';
    if (strlen($url) > 2000) return null;
    if (!filter_var($url, FILTER_VALIDATE_URL)) return null;
    $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
    return in_array($scheme, ['http', 'https'], true) ? $url : null;
}

function clean_visibility($v): string {
    return $v === 'public' ? 'public' : 'private';
}

// Returns an error message, or '' if everything is fine
function validate_item(string $title, string $description, ?string $url): string {
    if ($title === '')               return 'Title is required.';
    if (mb_strlen($title) > 255)     return 'Title must be 255 characters or less.';
    if (mb_strlen($description) > 5000) return 'Notes must be 5000 characters or less.';
    if ($url === null)               return 'Please enter a valid http:// or https:// link.';
    return '';
}