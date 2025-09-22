<?php

namespace App\Models;

use Froiden\RestAPI\ApiModel;

/**
 * App\Models\BaseModel
 *
 * @property-read mixed $icon
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BaseModel query()
 * @mixin \Eloquent
 */
class BaseModel extends ApiModel
{
    // Array of date fields that will be cast to Carbon instances
    // This can be overridden by child models
    protected $dates = [];

    /**
     * Generate HTML select options for a collection of items
     * Used for dropdown menus and form selects
     *
     * @param \Illuminate\Support\Collection|array $items Collection of model instances
     * @param object|null $group Selected group for default selection
     * @param string|null $columnName Alternative column name for display text
     * @return string HTML option string
     */
    public static function options($items, $group = null, $columnName = null): string
    {
        $options = '<option value="">--</option>';

        foreach ($items as $item) {

            $name = is_null($columnName) ? $item->name : $item->{$columnName};

            $selected = (!is_null($group) && ($item->id == $group->id)) ? 'selected' : '';

            $options .= '<option ' . $selected . ' value="' . $item->id . '"> ' . ($name) . ' </option>';
        }

        return $options;
    }

    /**
     * Generate clickable link with additional information display
     * Creates a formatted HTML block with link and subtitle
     *
     * @param string $route URL for the clickable link
     * @param string $title Main display text for the link
     * @param string|null $other Additional subtitle text
     * @return string HTML formatted clickable element
     */
    public static function clickAbleLink($route, $title, $other = null)
    {
        return '<div class="media align-items-center">
                        <div class="media-body">
                    <h5 class="mb-0 f-13 text-darkest-grey"><a href="' . $route . '" class="openRightModal">' . $title . '</a></h5>
                    <p class="mb-0">' . $other . '</p>
                    </div>
                  </div>';
    }

    /**
     * Override parent getDates() method to handle timestamp usage
     * Ensures proper date casting for models with and without timestamps
     *
     * @return array Array of date column names
     */
    // Added this for $dates
    public function getDates()
    {
        if (!$this->usesTimestamps()) {
            return $this->dates;
        }

        $defaults = [
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];

        return array_unique(array_merge($this->dates, $defaults));
    }

}