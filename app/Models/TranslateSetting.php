<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\TranslateSetting
 *
 * Represents translation-related settings for the application.
 * Currently stores API keys or credentials required for translation services (e.g., Google Translate).
 *
 * Properties:
 * @property int $id                     // Primary key
 * @property string|null $google_key     // Google Translate API key
 * @property \Illuminate\Support\Carbon|null $created_at // Record creation timestamp
 * @property \Illuminate\Support\Carbon|null $updated_at // Record last update timestamp
 *
 * Query Scopes / Methods:
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting newModelQuery() // Start a new model query
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting newQuery()      // Alias for query builder
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting query()         // Get query builder
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereCreatedAt($value) // Filter by created_at
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereGoogleKey($value) // Filter by google_key
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereId($value)        // Filter by id
 * @method static \Illuminate\Database\Eloquent\Builder|TranslateSetting whereUpdatedAt($value) // Filter by updated_at
 * @mixin \Eloquent
 */
class TranslateSetting extends BaseModel
{
    // Protect the 'id' field from mass assignment
    protected $guarded = ['id'];

    // Include factory methods for testing and seeding
    use HasFactory;
}
