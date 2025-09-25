<?php

namespace App\Observers;

use App\Helper\Files;
use App\Models\ProductFiles;

class ProductFileObserver
{

    public function saving(ProductFiles $productFiles)
    {
        // Track the user who last updated the product file
        if (!isRunningInConsoleOrSeeding() && user()) {
            $productFiles->last_updated_by = user()->id;
        }
    }

    public function creating(ProductFiles $productFiles)
    {
        // Track the user who added the product file
        if (!isRunningInConsoleOrSeeding() && user()) {
            $productFiles->added_by = user()->id;
        }

        // Assign the product file to the current company
        if (company()) {
            $productFiles->company_id = company()->id;
        }
    }

    public function deleting(ProductFiles $productFiles)
    {
        $productFiles->load('product');

        // Remove product's default image if it matches the file being deleted
        if (!isRunningInConsoleOrSeeding()) {
            if (isset($productFiles->product) && $productFiles->product->default_image == $productFiles->hashname) {
                $productFiles->product->default_image = null;
                $productFiles->product->save();
            }
        }

        // Delete the physical file from storage
        Files::deleteFile($productFiles->hashname, ProductFiles::FILE_PATH);
    }

}
