<?php

namespace App\Http\Requests\Post\Category;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\AccessLevel;


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
	    'name' => [
		'required',
                'image' => ['nullable', 'image', 'mimes:jpg,png,jpeg,gif,svg', 'max:2048', 'dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000'],
	    ],
        ];

	// It's a parent private category.
	if ($this->category->access_level == 'private' && !$this->category->isParentPrivate() && $this->category->canChangeAccessLevel()) {
	    // Only access level is settable.
	    $rules['access_level'] = 'required';
	}

        if ($this->category->canChangeStatus()) {
            $rules['status'] = 'required';
        }

	if ($this->category->access_level != 'private' && $this->category->canChangeAccessLevel()) {
	    $rules['access_level'] = 'required';
	    $rules['owned_by'] = 'required';
	}

	return $rules;
    }
}
