<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\GanttLink
 *
 * Represents a link/relationship between tasks in a Gantt chart.
 * Typically used to define dependencies (e.g., start-to-start, finish-to-start).
 *
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GanttLink query()
 *
 * @mixin \Eloquent
 */
class GanttLink extends BaseModel
{
    use HasFactory;
}
