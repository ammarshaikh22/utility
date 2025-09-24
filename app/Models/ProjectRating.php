<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\ProjectRating
 *
 * Represents a rating and feedback left by a user on a project.
 * Can include a numerical rating value and an optional comment.
 *
 * @property int $id
 * @property int $project_id ID of the related project
 * @property float $rating Numeric rating (e.g., 1–5 scale)
 * @property string|null $comment Optional user comment/feedback
 * @property int $user_id ID of the user who gave the rating
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when updated
 * @property int|null $added_by ID of the user who added this record
 * @property int|null $last_updated_by ID of the user who last updated this record
 *
 * @property-read \App\Models\Project $project Related project
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProjectRating whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class ProjectRating extends BaseModel
{
    /**
     * Relation: Get the project this rating belongs to
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
