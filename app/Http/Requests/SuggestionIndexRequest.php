<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuggestionIndexRequest extends FormRequest
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
            'keyword' => 'nullable|string|max:255',
            'source' => ['nullable', 'string', Rule::in(['all', 'planner', 'chat'])],
            'sort' => ['nullable', 'string', Rule::in(['created_at_desc', 'score_desc', 'score_asc'])],
        ];
    }
}
