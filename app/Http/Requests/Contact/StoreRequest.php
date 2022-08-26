<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\GoogleRecaptcha;

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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
	    'name' => 'bail|required|between:5,25|regex:/^[\pL\s\-]+$/u',
	    'email' => 'bail|required|email',
	    'object' => 'bail|required',
            'message' => 'required',
            'g-recaptcha-response' => ['required', new GoogleRecaptcha]
        ];
    }
}
