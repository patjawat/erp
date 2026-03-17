<?php

namespace app\modules\pdfTemplate\services;

use Yii;

/**
 * Resolves dot-notation paths against array/object data for PDF field values.
 * Supports: model attributes, nested relations, JSON paths (e.g. data_json.officer).
 * Missing segments return empty string (graceful degradation).
 */
class FieldValueResolver
{
    /**
     * Resolve a path like "officer_name", "createdByEmp.fullname", "data_json.location".
     *
     * @param array|object $data Root data (e.g. flat array or nested model-like array)
     * @param string $path Dot-separated path; empty returns ''
     * @return string Value to display; always string, empty if missing
     */
    public static function resolve($data, string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        $segments = array_filter(explode('.', $path), static function ($s) {
            return $s !== '';
        });
        if (empty($segments)) {
            return '';
        }
        $current = $data;
        foreach ($segments as $key) {
            $current = self::getSegment($current, $key);
            if ($current === null || $current === '') {
                return '';
            }
        }
        return self::stringify($current);
    }

    /**
     * @param mixed $current
     * @param string $key
     * @return mixed
     */
    private static function getSegment($current, string $key)
    {
        if ($current === null) {
            return null;
        }
        if (is_array($current)) {
            return $current[$key] ?? null;
        }
        if (is_object($current)) {
            if (property_exists($current, $key)) {
                return $current->$key;
            }
            if (method_exists($current, 'getAttribute')) {
                return $current->getAttribute($key);
            }
            if (method_exists($current, '__get')) {
                return $current->$key;
            }
            return null;
        }
        return null;
    }

    private static function stringify($value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return '';
    }
}
