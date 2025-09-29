<?php

namespace App\Observers;

use App\Models\ClientSubCategory;

class ClientSubCategoryObserver
{
    // Triggered before creating a new ClientSubCategory
    public function creating(ClientSubCategory $model)
    {
        // If a company is available, associate the sub-category with the company
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
