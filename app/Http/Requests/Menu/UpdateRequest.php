<?php

namespace App\Http\Requests\Menu\Menu;

use Illuminate\Foundation\Http\FormRequest;

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
        ];

        if ($this->menu->canChangeAccessLevel()) {
            $rules['access_level'] = 'required';
        }

        if ($this->menu->canChangeStatus()) {
            $rules['status'] = 'required';
        }

        if ($this->menu->canChangeAttachments()) {
            $rules['owned_by'] = 'required';
        }

        return $rules;
    }
}
