<?php

namespace App\Observers;

use App\Models\ProductCategory;

class ProductCategoryObserver
{

    public function creating(ProductCategory $productCategory)
    {
        // When creating a new product category, assign the current company ID
        if (company()) {
            $productCategory->company_id = company()->id;
        }
    }

}
