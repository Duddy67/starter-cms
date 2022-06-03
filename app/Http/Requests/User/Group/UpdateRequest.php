<?php

namespace App\Http\Requests\User\Group;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
		'regex:/^[a-z0-9-]{3,}$/',
		Rule::unique('groups')->ignore($this->group->id)
	    ]
        ];

	if (auth()->user()->getRoleLevel() > $this->group->getOwnerRoleLevel() || $this->group->owned_by == auth()->user()->id) {
	    $rules['access_level'] = 'required';
	    $rules['permission'] = 'required';
	    $rules['owned_by'] = 'required';
	}

	return $rules;
    }
}
