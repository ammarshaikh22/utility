<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class ExpenseImport implements ToArray
{
    /**
     * Define the fields for expense import.
     *
     * @return array The array of field definitions
     */
    public static function fields(): array
    {
        return [
            ['id' => 'item_name', 'name' => __('modules.expenses.itemName'), 'required' => 'Yes'],
            ['id' => 'price', 'name' => __('app.price'), 'required' => 'Yes'],
            ['id' => 'purchase_date', 'name' => __('modules.expenses.purchaseDate'), 'required' => 'Yes'],
            ['id' => 'email', 'name' => __('modules.employees.employeeEmail'), 'required' => 'No'],
            ['id' => 'purchase_from', 'name' => __('modules.expenses.purchaseFrom'), 'required' => 'No'],
            ['id' => 'description', 'name' => __('app.description'), 'required' => 'No'],
            ['id' => 'bank_account', 'name' => __('app.menu.bankaccount'), 'required' => 'No'],
            ['id' => 'category', 'name' => __('modules.expenses.expenseCategory'), 'required' => 'No'],
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