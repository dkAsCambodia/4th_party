<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

trait ApiFailedValidation
{
    protected function ApiFailedValidation(Validator $validator)
    {

        throw new HttpResponseException(
            response()->json(
                [
                    'message' => implode(", " ,$validator->errors()->all()),
                    'status' => false,
                ],
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            )
        );
    }
}
