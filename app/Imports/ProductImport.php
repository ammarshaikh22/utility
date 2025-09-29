<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ProductImport implements ToArray
{
    /**
     * Define the fields for product import.
     *
     * @return array The array of field definitions
     */
    public static function fields(): array
    {
        return [
            ['id' => 'product_name', 'name' => __('modules.client.productName'), 'required' => 'Yes'],
            ['id' => 'price', 'name' => __('app.price'), 'required' => 'Yes'],
            ['id' => 'unit_type', 'name' => __('modules.unitType.unitType'), 'required' => 'No'],
            ['id' => 'product_category', 'name' => __('modules.productCategory.productCategory'), 'required' => 'No'],
            ['id' => 'product_sub_category', 'name' => __('modules.productCategory.productSubCategory'), 'required' => 'No'],
            ['id' => 'sku', 'name' => __('app.sku'), 'required' => 'No'],
            ['id' => 'description', 'name' => __('app.description'), 'required' => 'No'],
        ];
    }

    /**
     * Return the input array as is.
     *
     * @param array $array The imported data array
     * @return array The input array
     */
    public function array(array $array): array
    {
        return $array;
    }
}