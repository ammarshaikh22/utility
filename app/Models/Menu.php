<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\Menu
 *
 * This model represents the menu items of the application.
 * Each menu record can optionally belong to a company (via HasCompany trait),
 * and defines menu metadata such as route, module, and icon.
 *
 * @property int $id Unique identifier for the menu item
 * @property string $menu_name Name of the menu item (display text)
 * @property string|null $translate_name Optional translation key or alternate display name
 * @property string|null $route Route/URL associated with the menu item
 * @property string|null $module The application module this menu item belongs to
 * @property string|null $icon Icon class or identifier used for displaying icons in UI
 * @property int|null $setting_menu Indicates if this is part of the settings menu (1 = yes, 0 = no)
 * @property int|null $company_id Associated company ID (if applicable)
 * @property \Illuminate\Support\Carbon|null $created_at Timestamp when the menu item was created
 * @property \Illuminate\Support\Carbon|null $updated_at Timestamp when the menu item was last updated
 *
 * @property-read \App\Models\Company|null $company The company related to this menu (via HasCompany trait)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereMenuName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereTranslateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSettingMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Menu extends BaseModel
{
    use HasCompany;

    /**
     * Attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];
}
