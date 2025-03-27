<?php

namespace App\Http\Requests;

use App\Rules\WithQuestionMark;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:10', 'max:1000', 'unique:questions,question', new WithQuestionMark()],
            'status'   => ['nullable', 'string', 'in:draft,published'],
        ];
    }
}
