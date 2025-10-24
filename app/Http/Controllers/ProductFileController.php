<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Traits\IconTrait;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Models\Product;
use App\Models\ProductFiles;
use Illuminate\Support\Facades\File;

class ProductFileController extends AccountBaseController
{
    use IconTrait;

    public function __construct()
    {
        parent::__construct();
        $this->pageIcon = 'icon-people';
        $this->pageTitle = 'app.menu.product';
    }

    /**
     * Store one or more files associated with a product.
     * Uploads files, saves their details, and sets the default image for the product if specified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {

            $defaultImage = null;

            foreach ($request->file as $fileData) {
                $file = new ProductFiles();
                $file->product_id = $request->product_id;

                $filename = Files::uploadLocalOrS3($fileData, ProductFiles::FILE_PATH);

                $file->filename = $fileData->getClientOriginalName();
                $file->hashname = $filename;
                $file->size = $fileData->getSize();
                $file->save();

                if ($fileData->getClientOriginalName() == $request->default_image) {
                    $defaultImage = $filename;
                }

            }

            $product = Product::findOrFail($request->product_id);
            $product->default_image = $defaultImage;
            $product->save();

        }

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Update images associated with a product.
     * Uploads new files, saves their details, and updates the default image for the product if specified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function updateImages(Request $request)
    {
        $defaultImage = null;

        if ($request->hasFile('file')) {
            foreach ($request->file as $file) {
                $productFile = new ProductFiles();
                $productFile->product_id = $request->product_id;
                $filename = Files::uploadLocalOrS3($file, 'products');
                $productFile->filename = $file->getClientOriginalName();
                $productFile->hashname = $filename;
                $productFile->size = $file->getSize();
                $productFile->save();

                if ($productFile->filename == $request->default_image) {
                    $defaultImage = $filename;
                }

            }
        }

        $product = Product::findOrFail($request->product_id);
        $product->default_image = $defaultImage ?: $request->default_image;
        $product->save();

        return Reply::success(__('messages.fileUploaded'));
    }

    /**
     * Delete a specific product file from storage.
     * Removes the file record associated with the given ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return array
     */
    public function destroy(Request $request, $id)
    {
        ProductFiles::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Download a specific product file.
     * Retrieves the file by ID and initiates a download from local or S3 storage.
     *
     * @param  int  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $file = ProductFiles::findOrFail($id);

        return download_local_s3($file, ProductFiles::FILE_PATH . '/' . $file->hashname);
    }

}