<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuggestionFromChatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string',
            'content' => 'required',
            'accommodation' => 'nullable|string',
            'local_food' => 'nullable|string',
            'itinerary' => 'required|array',
            'prefecture_code' => 'required|string|starts_with:JP-',
        ];
    }
}
