<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings\General;
use App\Traits\Admin\ItemConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Cache;

class GeneralController extends Controller
{
    use ItemConfig;

    /*
     * Instance of the model.
     */
    protected $model;

    /*
     * Name of the model.
     */
    protected $modelName = 'general';

    /*
     * Name of the plugin.
     */
    protected $pluginName = 'Settings';

    protected $app = ['name', 'timezone', 'env', 'debug', 'local', 'fallback_locale'];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.settings.general');
        $this->model = new General;
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
        $data = $this->model->getData();

        return view('admin.settings.general.form', compact('fields', 'actions', 'data', 'query'));
    }

    /**
     * Update the general parameters.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        $post = $request->except('_token', '_method');
        $this->truncateSettings();

        foreach ($post as $group => $params) {
          foreach ($params as $key => $value) {
              General::create(['group' => $group, 'key' => $key, 'value' => $value]);
          }
        }

        if (Cache::has('settings')) {
            // Delete the current "settings" variable so the config app parameters will be updated in the Admin middleware.
            Cache::forget('settings');
        }

        return redirect()->route('admin.settings.general.index', $request->query())->with('success', __('messages.general.update_success'));
    }

    /**
     * Empties the setting table.
     *
     * @return void
     */
    private function truncateSettings()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('settings_general')->truncate();
        Schema::enableForeignKeyConstraints();

        Artisan::call('cache:clear');
    }

    /*
     * Sets field values specific to the General model.
     *
     * @param  Array of stdClass Objects  $fields
     * @param  \App\Models\User\User  $user
     * @return void
     */
    private function setFieldValues(&$fields)
    {
        // Specific operations here...
    }
}
