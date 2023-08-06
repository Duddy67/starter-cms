<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cms\Setting;
use App\Traits\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Cache;

class SettingController extends Controller
{
    use Form;

    /*
     * Instance of the model.
     */
    protected $model;

    protected $app = ['name', 'timezone', 'env', 'debug', 'local', 'fallback_locale'];


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.cms.settings');
        $this->model = new Setting;
    }

    /**
     * Show the general settings.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $fields = $this->getFields();
        $actions = $this->getActions('form');
        $query = $request->query();
        $data = Setting::getData();

        return view('admin.cms.setting.form', compact('fields', 'actions', 'data', 'query'));
    }

    /**
     * Update the general parameters. (AJAX)
     *
     * @param  Request  $request
     * @return JSON
     */
    public function update(Request $request)
    {
        $post = $request->except('_token', '_method');
        $this->truncateSettings();

        foreach ($post as $group => $params) {
          foreach ($params as $key => $value) {
              Setting::create(['group' => $group, 'key' => $key, 'value' => $value]);
          }
        }

        if (Cache::has('settings')) {
            // Delete the current "settings" variable so the config app parameters will be updated in the Admin middleware.
            Cache::forget('settings');
        }

        return response()->json(['success' => __('messages.general.update_success')]);
    }

    /**
     * Empties the setting table.
     *
     * @return void
     */
    private function truncateSettings()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('settings')->truncate();
        Schema::enableForeignKeyConstraints();

        Artisan::call('cache:clear');
    }

    /*
     * Sets field values specific to the Setting model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\User  $user
     * @return void
     */
    private function setFieldValues(&$fields)
    {
        // Specific operations here...
    }
}
