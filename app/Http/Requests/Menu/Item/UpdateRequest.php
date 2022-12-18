<?php

namespace App\Http\Requests\Menu\Item;

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
        $rules = [
            'title' => 'required', 
            'url' => 'required',
            'model_name' => Rule::requiredIf(\Str::contains($this->url, ['{', '}'])),
            'status' => 'required',
        ];

        return $rules;
    }
}
