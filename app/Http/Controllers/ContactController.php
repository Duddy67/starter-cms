<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Email;
use App\Http\Requests\Contact\StoreRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactController extends Controller
{
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
