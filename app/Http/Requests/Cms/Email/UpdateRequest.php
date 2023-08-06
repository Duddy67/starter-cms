<?php

namespace App\Http\Requests\Cms\Email;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class UpdateRequest extends FormRequest
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
        $rules = ['subject' => 'required']; 

	if (auth()->user()->isSuperAdmin()) {
	    $rules = array_merge($rules, [
		'code' => [
		    'required',
		    'regex:/^[a-z0-9-]{3,}$/',
		    Rule::unique('emails')->ignore($this->email->id)
		],
		'body_html' => 'required_if:format,html',
		'body_text' => 'required_if:format,plain_text',
	    ]);
	}

	return $rules;
    }
}
