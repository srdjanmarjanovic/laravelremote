<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Allowed HTML tags for rich text content (position descriptions).
     */
    private const ALLOWED_RICH_TEXT_TAGS = '<p><br><strong><b><em><i><u><h2><h3><ul><ol><li><a>';

    /**
     * Sanitize HTML content by allowing only safe tags.
     * Used for rich text content like position descriptions.
     */
    public static function sanitizeRichText(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Remove script and style tags completely (including their content)
        $html = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $html);
        $html = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $html);

        // Strip all tags except allowed ones
        $sanitized = strip_tags($html, self::ALLOWED_RICH_TEXT_TAGS);

        // Remove any dangerous attributes from remaining tags
        $sanitized = self::removeDangerousAttributes($sanitized);

        return trim($sanitized);
    }

    /**
     * Strip all HTML tags from content.
     * Used for plain text fields like titles, custom questions, answers, etc.
     */
    public static function stripHtml(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        // Remove script and style tags completely (including their content)
        $content = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $content);
        $content = preg_replace('/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi', '', $content);

        // Strip all remaining HTML tags
        $content = strip_tags($content);

        // Decode HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($content);
    }

    /**
     * Remove dangerous attributes like onclick, onerror, javascript:, etc.
     */
    private static function removeDangerousAttributes(string $html): string
    {
        // Remove event handlers (onclick, onerror, onload, etc.) - match with or without quotes
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $html);

        // Remove javascript: protocol from href/src attributes
        $html = preg_replace('/\s*(href|src)\s*=\s*["\']javascript:[^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*(href|src)\s*=\s*javascript:[^\s>]*/i', '', $html);

        // Remove data: URLs that could contain scripts
        $html = preg_replace('/\s*(href|src)\s*=\s*["\']data:text\/html[^"\']*["\']/i', '', $html);

        // Ensure links only use http/https protocols
        $html = preg_replace_callback(
            '/<a\s+([^>]*?)>/i',
            function ($matches) {
                $attributes = $matches[1];
                // Remove any remaining event handlers
                $attributes = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $attributes);
                $attributes = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $attributes);

                // Extract href if present
                if (preg_match('/href\s*=\s*["\']([^"\']*)["\']/i', $attributes, $hrefMatch)) {
                    $href = $hrefMatch[1];
                    // Only allow http/https protocols or relative URLs
                    if (! preg_match('/^(https?:\/\/|\/|#)/i', $href)) {
                        // Remove href if it's not safe
                        $attributes = preg_replace('/href\s*=\s*["\'][^"\']*["\']/i', '', $attributes);
                    }
                }

                return '<a '.trim($attributes).'>';
            },
            $html
        );

        return $html;
    }
}
