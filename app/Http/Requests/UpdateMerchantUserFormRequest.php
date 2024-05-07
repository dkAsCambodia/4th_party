<?php

namespace App\Http\Requests;

use App\Traits\ApiFailedValidation;
use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMerchantUserFormRequest extends FormRequest
{
    use ApiFailedValidation;

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
            'name'      => 'required|string',
            'email'     => 'required|string|email',
            'password'  => 'required|string|confirmed',
            'password_confirmation' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'name is required',
            'email.required' => 'email is required',
            'password.required' => 'password is required',
            'password.confirmed' => 'password and password confirmation is not match',
        ];
    }
}
