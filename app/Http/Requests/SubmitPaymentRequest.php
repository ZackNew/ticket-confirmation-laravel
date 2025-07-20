<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => 'required|string|min:2|max:100',
            'email' => 'required|email|min:5|max:100',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(09|07)\d{8}$|^(\+?251(9|7)\d{8})$/'
            ],
            'image_link' => 'required|url',
            'address' => 'nullable|string|max:255',
            'number_of_tickets' => 'required|integer|min:1',
        ];
    }
}
