<?php

namespace App\Http\Requests\User\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User\Role;
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
		'not_regex:/^('.implode('|', Role::getDefaultRoles()).')$/i',
		'regex:/^[a-z0-9-]{3,}$/',
		Rule::unique('roles')->ignore($this->role->id)
	    ],
        ];

	if (auth()->user()->getRoleLevel() > $this->role->getOwnerRoleLevel() || $this->role->owned_by == auth()->user()->id) {
	    $rules['access_level'] = 'required';
	    $rules['owned_by'] = 'required';
	}

	return $rules;
    }
}
