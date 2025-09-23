<?php

namespace App\Models\SuperAdmin;

use App\Models\LanguageSetting;
use App\Models\BaseModel;

class FrontMenu extends BaseModel
{
    // Prevent mass assignment of the ID field
    protected $guarded = ['id'];

    // Specify the table name explicitly (not the default 'front_menus')
    protected $table = 'front_menu_buttons';

    /**
     * Defines a relationship to the LanguageSetting model.
     * Allows fetching the language associated with this menu item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(LanguageSetting::class, 'language_setting_id');
    }
}
