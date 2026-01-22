<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to sanitize all input data.
 * 
 * Sanitizes incoming request data by:
 * - Trimming whitespace from strings
 * - Converting empty strings to null
 * - Stripping dangerous HTML tags from certain fields
 * - Preventing XSS attacks
 */
class SanitizeInput
{
    /**
     * Fields that should not be sanitized.
     * 
     * @var array
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
    ];

    /**
     * Fields that allow safe HTML (for rich text editors).
     * These will be sanitized but allow certain tags.
     * 
     * @var array
     */
    protected array $allowHtml = [
        'description',
        'notes',
        'content',
    ];

    /**
     * Allowed HTML tags for rich text fields.
     * 
     * @var string
     */
    protected string $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><code><pre>';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only sanitize input for modifying requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $sanitizedInput = $this->sanitize($request->all());
            $request->merge($sanitizedInput);
        }

        return $next($request);
    }

    /**
     * Sanitize the given input.
     *
     * @param  array  $input
     * @param  string  $prefix
     * @return array
     */
    protected function sanitize(array $input, string $prefix = ''): array
    {
        $sanitized = [];

        foreach ($input as $key => $value) {
            $fieldKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                // Recursively sanitize arrays
                $sanitized[$key] = $this->sanitize($value, $fieldKey);
            } elseif (is_string($value)) {
                // Sanitize string values
                $sanitized[$key] = $this->sanitizeValue($value, $fieldKey);
            } else {
                // Keep other types as is
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize a single string value.
     *
     * @param  string  $value
     * @param  string  $key
     * @return string|null
     */
    protected function sanitizeValue(string $value, string $key): ?string
    {
        // Don't sanitize excepted fields
        if ($this->isExcepted($key)) {
            return $value;
        }

        // Trim whitespace
        $value = trim($value);

        // Convert empty strings to null
        if ($value === '') {
            return null;
        }

        // Check if field allows HTML
        if ($this->allowsHtml($key)) {
            // Strip all tags except allowed ones
            $value = strip_tags($value, $this->allowedTags);
            
            // Clean up any javascript: or data: URLs in href/src attributes
            $value = $this->cleanDangerousUrls($value);
        } else {
            // For regular fields, escape HTML entities
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            
            // Decode back to original if no special chars were found
            if (htmlspecialchars_decode($value) === $value) {
                // No special characters, keep original
            } else {
                // Has HTML entities, strip all tags
                $value = strip_tags($value);
            }
        }

        // Remove null bytes
        $value = str_replace("\0", '', $value);

        return $value;
    }

    /**
     * Check if a field is excepted from sanitization.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isExcepted(string $key): bool
    {
        // Get the field name without array notation
        $fieldName = preg_replace('/\.\d+\.?/', '.', $key);
        $fieldName = rtrim($fieldName, '.');
        $baseName = last(explode('.', $fieldName));

        return in_array($baseName, $this->except);
    }

    /**
     * Check if a field allows HTML.
     *
     * @param  string  $key
     * @return bool
     */
    protected function allowsHtml(string $key): bool
    {
        $baseName = last(explode('.', $key));

        return in_array($baseName, $this->allowHtml);
    }

    /**
     * Clean dangerous URLs from href and src attributes.
     *
     * @param  string  $value
     * @return string
     */
    protected function cleanDangerousUrls(string $value): string
    {
        // Remove javascript: and data: URLs from href and src attributes
        $patterns = [
            '/(<a[^>]*href=)["\']javascript:[^"\']*["\']/i' => '$1"#"',
            '/(<a[^>]*href=)["\']data:[^"\']*["\']/i' => '$1"#"',
            '/(<a[^>]*href=)["\']vbscript:[^"\']*["\']/i' => '$1"#"',
            '/(<[^>]*src=)["\']javascript:[^"\']*["\']/i' => '$1""',
            '/(<[^>]*src=)["\']data:[^"\']*["\']/i' => '$1""',
            '/on\w+\s*=\s*["\'][^"\']*["\']/i' => '', // Remove all inline event handlers
        ];

        foreach ($patterns as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value);
        }

        return $value;
    }
}
