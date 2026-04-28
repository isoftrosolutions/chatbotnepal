<?php

namespace App\Services;

class ChatButtonParser
{
    /**
     * Extract [link:Label|URL] and [btn:Label|Value] markup from AI response text.
     * Returns the cleaned message (syntax removed) and a buttons array.
     */
    public static function parse(string $text): array
    {
        $buttons = [];

        // Extract [link:Label|URL]
        $text = preg_replace_callback(
            '/\[link:([^\]|]+)\|([^\]]+)\]/u',
            function ($m) use (&$buttons) {
                $label = trim($m[1]);
                $url   = trim($m[2]);
                if ($label === '' || $url === '') {
                    return '';
                }
                // Block javascript: URLs
                if (preg_match('/^javascript:/i', $url)) {
                    return '';
                }
                $buttons[] = ['type' => 'link', 'label' => $label, 'url' => $url];
                return '';
            },
            $text
        ) ?? $text;

        // Extract [btn:Label|Value]
        $text = preg_replace_callback(
            '/\[btn:([^\]|]+)\|([^\]]+)\]/u',
            function ($m) use (&$buttons) {
                $label = trim($m[1]);
                $value = trim($m[2]);
                if ($label === '' || $value === '') {
                    return '';
                }
                $buttons[] = ['type' => 'reply', 'label' => $label, 'value' => $value];
                return '';
            },
            $text
        ) ?? $text;

        $text = preg_replace('/[ \t]+$/m', '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        return ['message' => $text, 'buttons' => $buttons];
    }
}
