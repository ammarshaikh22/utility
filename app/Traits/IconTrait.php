<?php

namespace App\Traits;

/**
 * Trait IconTrait
 *
 * This trait is used to dynamically determine the correct file icon
 * (Font Awesome class or custom image type) based on a file’s extension.
 */
trait IconTrait
{

    /**
     * The file name to check.
     * If not set, the trait will attempt to use `$this->hashname`.
     *
     * @var string|null
     */
    private $filename;

    /**
     * Mapping of file extensions to Font Awesome icon classes.
     *
     * @var array<string,string>
     */
    protected $mimeType = [
        // Text / code files
        'txt' => 'fa-file-alt',
        'htm' => 'fa-file-code',
        'html' => 'fa-file-code',
        'css' => 'fa-file-code-o',
        'js' => 'fa-file-code',
        'json' => 'fa-file-code',
        'xml' => 'fa-file-code',
        'swf' => 'fa-file',
        'CR2' => 'fa-file',
        'flv' => 'fa-file-video',

        // Images
        'png' => 'fa-file-image',
        'jpe' => 'fa-file-image',
        'jpeg' => 'fa-file-image',
        'jpg' => 'fa-file-image',
        'gif' => 'fa-file-image',
        'bmp' => 'fa-file-image',
        'ico' => 'fa-file-image',
        'tiff' => 'fa-file-image',
        'tif' => 'fa-file-image',
        'svg' => 'fa-file-image',
        'svgz' => 'fa-file-image',

        // Archives / executables
        'zip' => 'fa-file-archive',
        'rar' => 'fa-file-archive',
        'exe' => 'fa-file-archive',
        'msi' => 'fa-file-archive',
        'cab' => 'fa-file-archive',

        // Audio / video
        'mp3' => 'fa-file-audio',
        'qt' => 'fa-file-video',
        'mov' => 'fa-file-video',
        'mp4' => 'fa-file-video',
        'mkv' => 'fa-file-video',
        'avi' => 'fa-file-video',
        'wmv' => 'fa-file-video',
        'mpg' => 'fa-file-video',
        'mp2' => 'fa-file-video',
        'mpeg' => 'fa-file-video',
        'mpe' => 'fa-file-video',
        'mpv' => 'fa-file-video',
        '3gp' => 'fa-file-video',
        'm4v' => 'fa-file-video',
        'webm' => 'fa-file-video',

        // Adobe
        'pdf' => 'fa-file-pdf',
        'psd' => 'fa-file-image',
        'ai' => 'fa-file',
        'eps' => 'fa-file',
        'ps' => 'fa-file',

        // Microsoft Office
        'doc' => 'fa-file-alt',
        'rtf' => 'fa-file-alt',
        'xls' => 'fa-file-excel',
        'ppt' => 'fa-file-powerpoint',
        'docx' => 'fa-file-word',
        'xlsx' => 'fa-file-excel',
        'pptx' => 'fa-file-powerpoint',

        // Open Office
        'odt' => 'fa-file-alt',
        'ods' => 'fa-file-alt',
    ];

    /**
     * Dynamically get the icon class or type for the file.
     *
     * - If the file extension is an image type, returns "images".
     * - Otherwise, returns a Font Awesome icon class.
     *
     * @return string
     */
    public function getIconAttribute()
    {
        // Determine filename: use explicitly set value or fallback to model's hashname
        $filename = $this->filename ?? $this->hashname;

        // Extract and normalize the file extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // List of formats that should return "images"
        $imageFormats = ['png', 'jpe', 'jpeg', 'jpg', 'gif', 'bmp', 'ico', 'tif', 'svg', 'svgz', 'psd', 'csv'];

        if (in_array($ext, $imageFormats)) {
            return 'images';
        }

        // Fallback: return mapped icon or default text file icon
        return $this->mimeType[$ext] ?? 'fa-file-alt';
    }

}
