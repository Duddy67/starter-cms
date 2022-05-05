<?php

namespace App\Http\Requests\Settings\Email;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
	    'code' => [
		'required',
		'regex:/^[a-z0-9_]{3,}$/',
		'unique:emails'
	    ],
	    'subject' => 'required',
	    'body_html' => 'required_if:format,html',
	    'body_text' => 'required_if:format,plain_text',
        ];
    }
}
