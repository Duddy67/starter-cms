<?php

namespace App\Http\Controllers\Admin\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post\Setting;
use App\Traits\Admin\Form;
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin.post.settings');
        $this->model = new Setting;
    }


    /**
     * Show the post settings.
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request, $tab = null)
    {
        $fields = $this->getFields();
        $actions = $this->getActions('form');
        $query = $request->query();
        $data = Setting::getData();
        $tab = ($tab) ? $tab : 'posts';

        return view('admin.post.setting.form', compact('fields', 'actions', 'data', 'tab', 'query'));
    }

    /**
     * Update the post parameters.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        $post = $request->except('_token', '_method', '_tab');
        $this->truncateSettings();

        foreach ($post as $group => $params) {
          foreach ($params as $key => $value) {
              Setting::create(['group' => $group, 'key' => $key, 'value' => $value]);
          }
        }

        return redirect()->route('admin.post.settings.index', array_merge($request->query(), ['tab' => $request->input('_tab')]))->with('success', __('messages.general.update_success'));
    }

    /**
     * Empties the setting table.
     *
     * @return void
     */
    private function truncateSettings()
    {
        Schema::disableForeignKeyConstraints();
        DB::table('post_settings')->truncate();
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
