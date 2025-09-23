<?php

namespace App\Models\SuperAdmin;

use App\Models\BaseModel;

/**
 * App\Models\SuperAdmin\FrontWidget
 *
 * @property int $id
 * @property string $name
 * @property string $widget_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|FrontWidget newModelQuery()
 * @method static Builder|FrontWidget newQuery()
 * @method static Builder|FrontWidget query()
 * @method static Builder|FrontWidget whereCreatedAt($value)
 * @method static Builder|FrontWidget whereId($value)
 * @method static Builder|FrontWidget whereName($value)
 * @method static Builder|FrontWidget whereUpdatedAt($value)
 * @method static Builder|FrontWidget whereWidgetCode($value)
 * @mixin Eloquent
 */
class FrontWidget extends BaseModel
{
    // Prevent mass assignment of the ID field
    protected $guarded = ['id'];

    /**
     * You can add custom methods here to render the widget
     * or to transform the widget_code if needed.
     */
}