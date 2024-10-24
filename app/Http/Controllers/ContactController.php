<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cms\Category;
use App\Models\Cms\Setting;
use App\Models\Cms\Email;
use App\Http\Requests\Contact\StoreRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactController extends Controller
{

    public function show(Request $request)
    {
        $page = Setting::getPage('contact');

        // Check if a 'contact' category exists.
	if (!$category = Category::where('slug', $page['name'])->first()) {
            // If no 'contact' category is found, just display the contact page.
            return view('themes.'.$page['theme'].'.index', compact('page'));
        }

        // Collect some extra data.
        $metaData = $category->meta_data;
        $query = $request->query();

        return view('themes.'.$page['theme'].'.index', compact('page', 'category', 'metaData', 'query'));
    }

    /**
     * Store a newly sent message through the contact form. (AJAX)
     *
     * @param  \App\Http\Requests\Contact\StoreRequest $request
     * @return JSON
     */
    public function store(StoreRequest $request)
    {
        $id = DB::table('contacts')->insertGetId([
            'name' => $request->input('name'), 
            'email' => $request->input('email'), 
            'object' => $request->input('object'), 
            'message' => $request->input('message'), 
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'status' => 'unread'
        ]);

        $contact = DB::table('contacts')->where('id', $id)->first();

        // Set a recipient attribute to prevent the sendEmail function to use the email
        // attribute as recipient.
        $contact->recipient = Setting::getValue('website', 'admin_email');

        if (!empty($contact->recipient)) {
            if (Email::sendEmail('new-message', $contact)) {
                $request->session()->flash('success', __('messages.message.send_success'));
            }
            else {
                $request->session()->flash('error', __('messages.message.send_error'));
            }
        }


        return response()->json();
    }
}
