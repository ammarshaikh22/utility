<?php

namespace App\Http\Controllers;

use App\Helper\Common;

class FileController extends Controller
{

    /**
     * Retrieve a file or image from the server or S3 storage.
     *
     * This function:
     *  - Ensures the requested type is valid (only 'file' or 'image').
     *  - Decrypts the provided path (which was previously encrypted for security).
     *  - Redirects the user to the actual file location (local or S3).
     *  - Returns a 404 error if the file cannot be found or the path is invalid.
     *
     * @param string $type  The type of file being requested ('file' or 'image')
     * @param string $path  The encrypted file path
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function getFile($type, $path)
    {
        // Abort with 404 if file type is invalid
        abort_if(!in_array($type, ['file', 'image']), 404);

        try {
            // Remove "_masked.png" if it exists (used for masked image thumbnails)
            $path = str($path)->replace('_masked.png', '')->__toString();

            // Decrypt the file path using the application's encryption helper
            $decrypted = Common::encryptDecrypt($path, 'decrypt');

            // Redirect to the actual file URL (could be local or S3)
            return response()->redirectTo(asset_url_local_s3($decrypted));
        } catch (\Exception $e) {
            // If decryption or file access fails, return a 404 error
            abort(404);
        }
    }

}
