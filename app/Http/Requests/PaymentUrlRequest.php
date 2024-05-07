<?php

namespace App\Http\Requests;

use App\Traits\FailedValidation;
use Illuminate\Foundation\Http\FormRequest;

class PaymentUrlRequest extends FormRequest
{
    use FailedValidation;

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
            'merchant_code' => 'required|exists:merchants,merchant_code',
            'customer_id' => 'required',
           
            'product_id' => 'required|exists:payment_maps,product_id',
        ];
    }

    public function messages()
    {
        return [
            'merchant_code.required' => 'Merchant Code  required',
            'merchant_code.exists' => 'Merchant Code not exists',
            'product_id.required' => 'Product Id required',
            'product_id.exists' => 'Product not exists',
          
            'customer_id.required' => 'Customer Id is required',
        ];
    }
}
