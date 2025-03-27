<?php

namespace App\Http\Requests;

use App\Rules\{OnlyAsDraft, WithQuestionMark};
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * @property-read string $question
 */
class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \App\Models\Question $question */
        $question = $this->route('question');

        return Gate::allows('update', $question);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var \App\Models\Question $question */
        $question = $this->route('question');

        return [
            'question' => [
                'required',
                'string',
                'min:10',
                'max:1000',
                Rule::unique('questions', 'question')->ignore($question),
                new WithQuestionMark(),
                new OnlyAsDraft(question: $question),
            ],
            'status' => ['nullable', 'string', 'in:draft,published'],
        ];
    }
}
