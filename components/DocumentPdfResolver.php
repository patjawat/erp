<?php

namespace app\components;

/**
 * Resolves a readable PDF from upload metadata without trusting stale rows.
 */
final class DocumentPdfResolver
{
    /**
     * @param iterable<object> $uploads newest uploads should be provided first
     * @return array{path:string, downloadName:string, upload:object}|null
     */
    public static function resolve(iterable $uploads, string $uploadRoot): ?array
    {
        $uploadRoot = rtrim($uploadRoot, "/\\");

        foreach ($uploads as $upload) {
            $ref = trim((string) ($upload->ref ?? ''));
            $realFilename = trim((string) ($upload->real_filename ?? ''));
            $originalFilename = trim((string) ($upload->file_name ?? ''));
            if (!self::isSafePathSegment($ref) || !self::isSafePathSegment($realFilename)) {
                continue;
            }

            $extension = strtolower((string) pathinfo($realFilename, PATHINFO_EXTENSION));
            if ($extension !== 'pdf') {
                continue;
            }

            $path = $uploadRoot . DIRECTORY_SEPARATOR . $ref . DIRECTORY_SEPARATOR . $realFilename;
            if (!is_file($path) || !is_readable($path)) {
                continue;
            }

            $downloadName = self::isSafePathSegment($originalFilename) && $originalFilename !== ''
                ? $originalFilename
                : $realFilename;

            return [
                'path' => $path,
                'downloadName' => $downloadName,
                'upload' => $upload,
            ];
        }

        return null;
    }

    private static function isSafePathSegment(string $value): bool
    {
        return $value !== ''
            && $value !== '.'
            && $value !== '..'
            && basename($value) === $value
            && !str_contains($value, '/')
            && !str_contains($value, '\\');
    }
}
