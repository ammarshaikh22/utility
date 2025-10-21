<?php

namespace App\Constants;

/**
 * FileInfo - Centralized configuration for file paths and image sizes
 * Used by FileManager class to standardize asset handling across the application
 */
class FileInfo
{
    /**
     * Returns an array of file paths and image size configurations
     *
     * @return array
     */
    public function fileInfo()
    {
        return [
            // === VERIFICATION IMAGES ===
            'withdrawVerify' => [
                'path' => 'assets/images/verify/withdraw' // Withdrawal verification documents
            ],
            'depositVerify' => [
                'path' => 'assets/images/verify/deposit'  // Deposit verification documents
            ],
            'verify' => [
                'path' => 'assets/verify'                // General verification files
            ],

            // === DEFAULT & SYSTEM IMAGES ===
            'default' => [
                'path' => 'assets/images/default.png'    // Default fallback image
            ],
            'favicon' => [
                'size' => '128x128'                      // Favicon dimensions
            ],
            'logoIcon' => [
                'path' => 'assets/images/logo_icon'      // Logo/icon assets
            ],

            // === SUPPORT & NOTIFICATIONS ===
            'ticket' => [
                'path' => 'assets/support'               // Support ticket attachments
            ],
            'push' => [
                'path' => 'assets/images/push_notification' // Push notification images
            ],

            // === SEO & UI ===
            'seo' => [
                'path' => 'assets/images/seo',           // SEO meta images
                'size' => '1180x600'                     // Optimized for social sharing
            ],
            'maintenance' => [
                'path' => 'assets/images/maintenance',   // Maintenance mode images
                'size' => '660x325'                      // Maintenance page display
            ],

            // === USER & ADMIN PROFILES ===
            'userProfile' => [
                'path' => 'assets/images/user/profile',  // User profile pictures
                'size' => '350x300'                      // Standard profile image size
            ],
            'userSignature' => [
                'path' => 'assets/images/user/signature', // User digital signatures
                'size' => '100x50'                       // Compact signature size
            ],
            'adminProfile' => [
                'path' => 'assets/admin/images/profile', // Admin profile pictures
                'size' => '400x400'                      // Square admin avatar
            ],

            // === SYSTEM EXTENSIONS & GATEWAYS ===
            'extensions' => [
                'path' => 'assets/images/extensions',    // File type icons
                'size' => '36x36'                        // Small icon size
            ],
            'gateway' => [
                'path' => 'assets/images/gateway',       // Payment gateway logos
                'size' => ''                             // Variable sizes
            ],
            'withdrawMethod' => [
                'path' => 'assets/images/withdraw_method', // Withdrawal method icons
                'size' => ''                             // Variable sizes
            ],

            // === PROPERTY & LOCATION ===
            'propertyThumb' => [
                'path' => 'assets/images/property/thumb', // Property thumbnails
                'size' => '555x370',                     // Main thumbnail size
                'thumb' => '120x80'                      // Mini thumbnail size
            ],
            'propertyGallery' => [
                'path' => 'assets/images/property/gallery', // Property gallery images
                'size' => '840x450'                      // Large gallery display
            ],
            'location' => [
                'path' => 'assets/images/location',      // Location images/maps
                'size' => '305x395'                      // Location preview size
            ],

            // === MISC ===
            'language' => [
                'path' => 'assets/images/language',      // Language flag icons
                'size' => '50x50'                        // Small flag size
            ],
            'pushConfig' => [
                'path' => 'assets/admin'                 // Admin push notification configs
            ],
        ];
    }
}