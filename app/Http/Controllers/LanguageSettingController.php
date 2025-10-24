<?php

namespace App\Http\Controllers;

use App\Models\Flag;
use App\Helper\Reply;
use App\Models\GlobalSetting;
use Illuminate\Http\Request;
use App\Models\LanguageSetting;
use App\Models\TranslateSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\Admin\Language\StoreRequest;
use App\Http\Requests\Admin\Language\UpdateRequest;
use Barryvdh\TranslationManager\Models\Translation;
use App\Http\Requests\Admin\Language\AutoTranslateRequest;

class LanguageSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.languageSettings';
        $this->activeSettingMenu = 'language_settings';
        $this->langPath = base_path() . '/resources/lang';
        $this->middleware(function ($request, $next) {
            abort_403(((user()->permission('manage_language_setting') !== 'all') && GlobalSetting::validateSuperAdmin('manage_superadmin_language_settings')));
            return $next($request);
        });
    }

    /**
     * Display a listing of all language settings.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->languages = LanguageSetting::all();
        return view('language-settings.index', $this->data);
    }

    /**
     * Update the status of a language setting.
     * Updates the specified language setting's status and saves it.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function update(Request $request, $id)
    {
        $setting = LanguageSetting::findOrFail($request->id);

        if ($request->has('status')) {
            $setting->status = $request->status;
        }

        $setting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Update a language setting's details.
     * Updates language name, code, flag, status, and RTL setting, and handles language folder renaming.
     *
     * @param \App\Http\Requests\Admin\Language\UpdateRequest $request
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function updateData(UpdateRequest $request, $id)
    {
        $setting = LanguageSetting::findOrFail($request->id);

        $oldLangExists = File::exists($this->langPath.'/'.$setting->language_code);

        if($oldLangExists){
            // check and create lang folder
            $langExists = File::exists($this->langPath . '/' . $request->language_code);

            if (!$langExists) {
                // update lang folder name
                File::move($this->langPath . '/' . $setting->language_code, $this->langPath . '/' . $request->language_code);

                Translation::where('locale', $setting->language_code)->get()->map(function ($translation) {
                    $translation->delete();
                });
            }
        }

        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->flag_code = strtolower($request->flag);
        $setting->status = $request->status;
        $setting->is_rtl = $request->is_rtl;
        $setting->save();

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Store a new language setting.
     * Creates a new language folder and saves the language details.
     *
     * @param \App\Http\Requests\Admin\Language\StoreRequest $request
     * @return \App\Helper\Reply
     */
    public function store(StoreRequest $request)
    {
        // check and create lang folder
        $langExists = File::exists($this->langPath . '/' . $request->language_code);

        if (!$langExists) {
            File::makeDirectory($this->langPath . '/' . $request->language_code);
        }

        $setting = new LanguageSetting();
        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->flag_code = $request->flag;
        $setting->status = $request->status;
        $setting->is_rtl = $request->is_rtl;
        $setting->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for creating a new language setting.
     * Retrieves available flags for selection.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $this->flags = Flag::get();

        return view('language-settings.create-language-settings-modal', $this->data);
    }

    /**
     * Show the form for auto-translation settings.
     * Retrieves the current translation settings for display.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function autoTranslate(Request $request)
    {
        $this->translateSetting = TranslateSetting::first();
        return view('language-settings.auto-translate-modal', $this->data);
    }

    /**
     * Update auto-translation settings.
     * Validates and updates the translation settings.
     *
     * @param \App\Http\Requests\Admin\Language\AutoTranslateRequest $request
     * @return \App\Helper\Reply
     */
    public function autoTranslateUpdate(AutoTranslateRequest $request)
    {
        $translateSetting = TranslateSetting::first();
        $translateSetting->update($request->validated());

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Show the form for editing an existing language setting.
     * Retrieves the language setting and available flags for selection.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $id)
    {
        $this->languageSetting = LanguageSetting::findOrFail($id);
        $this->flags = Flag::get();

        return view('language-settings.edit-language-settings-modal', $this->data);
    }

    /**
     * Delete a language setting and its associated resources.
     * Removes the language folder, translations, and updates the default locale if necessary.
     *
     * @param int $id
     * @return \App\Helper\Reply
     */
    public function destroy($id)
    {
        $language = LanguageSetting::findOrFail($id);
        $setting = companyOrGlobalSetting();

        if ($language->language_code == $setting->locale) {
            $setting->locale = 'en';
            $setting->last_updated_by = $this->user->id;
            $setting->save();
            session()->forget('user');
        }

        $language->destroy($id);

        $langExists = File::exists($this->langPath . '/' . $language->language_code);

        if ($langExists) {
            File::deleteDirectory($this->langPath . '/' . $language->language_code);
        }

        if (Schema::hasTable('ltm_translations')) {
            DB::statement('DELETE FROM ltm_translations where locale = "'.$language->language_code.'"');
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    /**
     * Reset and re-import translations.
     * Executes artisan commands to reset and import translations.
     *
     * @return \App\Helper\Reply
     */
    public function fixTranslation()
    {
        Artisan::call('translations:reset');
        Artisan::call('translations:import');
        return Reply::success(__('modules.languageSettings.fixTranslationSuccess'));
    }

    /**
     * Create an English locale by copying existing language resources.
     * Copies the 'eng' folder and JSON file to create an 'en' locale.
     *
     * @return \App\Helper\Reply
     */
    public function createEnLocale()
    {
        // copy eng folder from resources/lang to resources/lang/en
        File::copyDirectory($this->langPath . '/eng', $this->langPath . '/en');

        // copy eng.json file from resources/lang to resources/lang/en.json
        File::copy($this->langPath . '/eng.json', $this->langPath . '/en.json');

        return Reply::success(__('messages.recordSaved'));
    }

}