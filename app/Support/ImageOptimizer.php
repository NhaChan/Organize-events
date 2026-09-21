<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImageOptimizer
{
    public static function store(UploadedFile $file, string $directory, int $maxDimension = 1920): string
    {
        if (! function_exists('imagewebp') || $file->getMimeType() === 'image/gif') {
            return $file->store($directory, 'public');
        }

        try {
            $binary = file_get_contents($file->getRealPath());
            $optimized = self::encode($binary, $maxDimension);

            if ($optimized === null) {
                return $file->store($directory, 'public');
            }

            $path = trim($directory, '/').'/'.Str::uuid().'.webp';
            Storage::disk('public')->put($path, $optimized);

            return $path;
        } catch (Throwable $exception) {
            report($exception);

            return $file->store($directory, 'public');
        }
    }

    public static function optimizeStored(string $path, int $maxDimension = 1920): string
    {
        $disk = Storage::disk('public');

        if (! function_exists('imagewebp') || ! $disk->exists($path) || Str::endsWith(Str::lower($path), '.gif')) {
            return $path;
        }

        try {
            $original = $disk->get($path);
            $optimized = self::encode($original, $maxDimension);

            if ($optimized === null || strlen($optimized) >= strlen($original)) {
                return $path;
            }

            $newPath = preg_replace('/\.[^.\/]+$/', '', $path).'.webp';
            if ($newPath === $path) {
                return $path;
            }

            $disk->put($newPath, $optimized);

            return $newPath;
        } catch (Throwable $exception) {
            report($exception);

            return $path;
        }
    }

    private static function encode(string $binary, int $maxDimension): ?string
    {
        $source = @imagecreatefromstring($binary);

        if (! $source) {
            return null;
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);
            $scale = min(1, $maxDimension / max($width, $height));
            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));
            $canvas = imagecreatetruecolor($targetWidth, $targetHeight);

            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

            ob_start();
            $written = imagewebp($canvas, null, 82);
            $result = ob_get_clean();
            imagedestroy($canvas);

            return $written && is_string($result) ? $result : null;
        } finally {
            imagedestroy($source);
        }
    }
}
