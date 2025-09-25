<?php

namespace App\Observers;

use App\Models\ClientCategory;

class ClientCategoryObserver
{
    // Triggered before creating a new ClientCategory
    public function creating(ClientCategory $model)
    {
        // If a company is available, associate the category with the company
        if (company()) {
            $model->company_id = company()->id;
        }
    }

}
