<?php

namespace App\Http\Requests\Menu\Menu;

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
            'title' => 'required',
            'code' => [
                'required',
                'regex:/^[a-z0-9-]{3,}$/',
                'unique:menus'
            ],
            'status' => 'required',
            'access_level' => 'required',
            'owned_by' => 'required',
        ];
    }
}
