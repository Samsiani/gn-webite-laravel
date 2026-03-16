<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

class ConvertMediaToWebp
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        // Only convert product images (images + gallery collections)
        if (! in_array($media->collection_name, ['images', 'gallery'])) {
            return;
        }

        $path = $media->getPath();

        if (! file_exists($path)) {
            return;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        // Already WebP — skip
        if ($extension === 'webp') {
            return;
        }

        // Load original image
        $image = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'gif' => @imagecreatefromgif($path),
            'bmp' => @imagecreatefrombmp($path),
            default => null,
        };

        if (! $image) {
            return;
        }

        // Preserve alpha for PNG
        if ($extension === 'png') {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        // Build new WebP path
        $newFilename = pathinfo($media->file_name, PATHINFO_FILENAME) . '.webp';
        $newPath = dirname($path) . '/' . $newFilename;

        // Convert to WebP (quality 90)
        imagewebp($image, $newPath, 90);
        imagedestroy($image);

        // Remove old file if different path
        if ($path !== $newPath && file_exists($newPath)) {
            @unlink($path);
        }

        // Update media record
        $media->file_name = $newFilename;
        $media->mime_type = 'image/webp';
        $media->size = filesize($newPath);
        $media->save();
    }
}
