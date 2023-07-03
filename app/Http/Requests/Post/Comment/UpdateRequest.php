<?php

namespace App\Http\Requests\Post\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;


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
        return [
            'comment-'.$this->comment->id => 'required',
        ];
    }

    public function messages()
    {
        return [
            'comment-'.$this->comment->id.'.required' => 'Your comment cannot be empty.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
              'errors' => $validator->errors(), 
              'status' => true, 
              'commentId' => $this->comment->id,
              'message' => __('messages.generic.form_errors')
        ], 422));
    }
}
