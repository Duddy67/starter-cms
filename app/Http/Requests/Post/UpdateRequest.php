<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\Admin\AccessLevel;


class UpdateRequest extends FormRequest
{
    use AccessLevel;

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
            'content' => 'required',
            'image' => ['nullable', 'image', 'mimes:jpg,png,jpeg,gif,svg', 'max:2048', 'dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000'],
        ];

        if ($this->post->canChangeAccessLevel()) {
            $rules['access_level'] = 'required';
        }

        if ($this->post->canChangeStatus()) {
            // Some rules don't apply whith the API.
            $rules['status'] = $this->wantsJson() ? '' : 'required';
        }

        if ($this->post->canChangeAttachments()) {
            // Some rules don't apply whith the API.
            $rules['owned_by'] = $this->wantsJson() ? '' : 'required';
        }

        return $rules;
    }
}
