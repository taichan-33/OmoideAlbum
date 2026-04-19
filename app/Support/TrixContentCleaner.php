<?php

namespace App\Support;

class TrixContentCleaner
{
    public static function clean(?string $htmlContent): string
    {
        $cleaned = preg_replace('/<figure data-trix-attachment=".*?<\/figure>/s', '', $htmlContent ?? '');
        $text = strip_tags($cleaned ?? '');
        $text = preg_replace('/(\s\s+)/', ' ', $text);

        return trim($text ?? '');
    }
}
