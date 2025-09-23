<?php

namespace App\Traits;

use App\Helper\Common;
use App\Models\StorageSetting;
use Exception;

/**
 * Trait HasMaskImage
 *
 * Provides a utility method for generating a secure "masked" image URL.
 * 
 * - For local storage or S3-compatible storage, it generates a direct asset URL.
 * - For standard S3 and other non-local storages, it generates a masked/secure route.
 */
trait HasMaskImage
{

    /**
     * Generate a masked (secure) image URL for the given path.
     *
     * Behavior:
     * - If using local storage or S3-compatible storage → returns the normal local/S3 asset URL.
     * - Otherwise → generates a secure masked URL with encryption applied.
     * 
     * @param string $path Path of the image file.
     * 
     * @return string Masked image URL that can be used for secure rendering.
     */
    private function generateMaskedImageAppUrl($path): string
    {
        // If default filesystem is NOT one of the S3-compatible storages,
        // return a normal asset URL (no masking needed).
        if (!in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            return asset_url_local_s3($path);
        }

        // Encrypt file path to generate a masked image file name
        $filePath = Common::encryptDecrypt($path) . '_masked.png';

        try {
            // Try generating a secure route-based URL
            return route('file.getFile', ['type' => 'image', 'path' => $filePath]);
        } catch (Exception $exception) {
            // Fallback: build the URL manually if route fails
            return config('app.url') . '/file/image/' . $filePath;
        }
    }

}
