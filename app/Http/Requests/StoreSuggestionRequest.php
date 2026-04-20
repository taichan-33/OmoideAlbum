<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuggestionRequest extends FormRequest
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
            'optional_destination' => 'nullable|string|max:255',
            'optional_season' => 'nullable|string|max:255',
            'optional_budget' => 'nullable|string|max:255',
            'optional_interest' => 'nullable|string|max:255',
            'optional_memo' => 'nullable|string|max:500',
        ];
    }
}
