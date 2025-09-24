<?php

namespace App\Models;

use App\Traits\HasCompany;

/**
 * App\Models\ThemeSetting
 *
 * Stores the theme and UI-related settings for the application panel.
 *
 * @property int $id
 * @property string $panel                    // Panel type (e.g., admin, user)
 * @property string $header_color             // Color of the header
 * @property string $sidebar_color            // Background color of the sidebar
 * @property string $sidebar_text_color       // Text color in the sidebar
 * @property int $restrict_admin_theme_change // 1 if admin cannot change theme, 0 otherwise
 * @property string $link_color               // Default link color
 * @property string|null $user_css            // Optional custom CSS provided by the user
 * @property string $sidebar_theme            // Sidebar theme (light, dark, etc.)
 * @property int|null $company_id             // Associated company
 * @property int $enable_rounded_theme        // 1 if rounded theme is enabled
 * @property string|null $login_background    // Background image for login screen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $icon
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereHeaderColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereLinkColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting wherePanel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarTextColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereSidebarTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereUserCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereEnableRoundedTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThemeSetting whereLoginBackground($value)
 * @mixin \Eloquent
 */
class ThemeSetting extends BaseModel
{
    use HasCompany; // Adds company relationship and related helper methods

    // Currently, no additional methods; only stores theme settings for companies or panels
}
