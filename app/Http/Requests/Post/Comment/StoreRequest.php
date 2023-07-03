<?php

namespace App\Http\Requests\Post\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

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
            'comment-0' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'comment-0.required' => 'Your comment cannot be empty.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
              'errors' => $validator->errors(), 
              'status' => true, 
              'commentId' => 0,
              'message' => __('messages.generic.form_errors')
        ], 422));
    }
}
