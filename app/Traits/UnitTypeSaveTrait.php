<?php

namespace App\Traits;

use App\Models\UnitType;

/**
 * Trait UnitTypeSaveTrait
 *
 * Ensures that a model has a valid unit type assigned.
 * If a model doesn't already have a `unit_id`, this trait
 * will automatically assign the first available `UnitType`
 * for the given company.
 */
trait UnitTypeSaveTrait
{
    /**
     * Assigns a default unit type to the given model if not already set.
     *
     * @param  mixed  $model  The model instance that should have a unit type.
     * @return mixed          The model instance with a unit type assigned (if applicable).
     */
    public function unitType($model)
    {
        // If the model already has a unit_id, skip processing
        if (!is_null($model->unit_id)) {
            return $model;
        }

        // Get the first unit type available for the company
        $type = UnitType::where('company_id', $model->company_id)->first();

        // If a unit type exists, assign its ID to the model
        if ($type) {
            $model->unit_id = $type->id;
        }

        // Return the model (with or without unit_id updated)
        return $model;
    }
}
