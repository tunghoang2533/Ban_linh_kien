<?php
namespace App\Helpers;

class UploadHelper {
    private const IMAGE_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    public static function storeImage(array $file, string $uploadDir, string $prefix = 'img_', int $maxSize = 2097152, array $allowedMimes = []): ?string {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxSize) {
            return null;
        }

        $allowedMimes = $allowedMimes ?: array_keys(self::IMAGE_MIME_EXTENSIONS);
        $mimeType = self::detectMime($file['tmp_name']);
        if (!in_array($mimeType, $allowedMimes, true) || empty(self::IMAGE_MIME_EXTENSIONS[$mimeType])) {
            return null;
        }

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
            return null;
        }

        $extension = self::IMAGE_MIME_EXTENSIONS[$mimeType];
        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix) ?: 'img_';
        $filename = $safePrefix . time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        return move_uploaded_file($file['tmp_name'], rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename) ? $filename : null;
    }

    public static function normalizeMultiFile(array $files, int $index): array {
        return [
            'name'     => $files['name'][$index] ?? '',
            'type'     => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error'    => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $files['size'][$index] ?? 0,
        ];
    }

    private static function detectMime(string $tmpPath): string {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            return '';
        }
        $mimeType = finfo_file($finfo, $tmpPath) ?: '';
        finfo_close($finfo);
        return $mimeType;
    }
}
