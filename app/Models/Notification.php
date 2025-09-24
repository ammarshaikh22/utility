<?php

namespace App\Models;

/**
 * App\Models\Notification
 *
 * Represents a system notification stored in the database.
 * 
 * This table follows Laravel’s default `notifications` structure, where each notification
 * can be polymorphically associated with a "notifiable" entity (usually a User or Company).
 *
 * @property int $id
 * @property string $type                 The notification class type (e.g., App\Notifications\TaskCreated)
 * @property string $notifiable_type      The related model class (polymorphic relation, e.g., App\Models\User)
 * @property int $notifiable_id           The ID of the notifiable model instance
 * @property string $data                 JSON payload with notification details
 * @property string|null $read_at         When the notification was marked as read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read mixed $icon
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification query()
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereNotifiableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Notification whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Notification extends BaseModel
{
    /**
     * Delete unread notifications of specific types related to a given entity.
     *
     * This is often used when an entity (like a task, project, or notice)
     * is deleted or updated and we need to remove pending/unread notifications.
     *
     * @param array $notifyData  Array of notification class types to filter (e.g., [TaskCreated::class])
     * @param int   $id          The related entity ID inside the `data` JSON field
     * 
     * @return void
     */
    public static function deleteNotification($notifyData, $id): void
    {
        Notification::whereIn('type', $notifyData)
            ->whereNull('read_at') // Only delete if not read
            ->where('data', 'like', '{"id":' . $id . ',%') // Match JSON payload starting with {"id":$id,...
            ->delete();
    }
}
