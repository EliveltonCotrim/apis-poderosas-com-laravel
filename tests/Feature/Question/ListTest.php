<?php

use App\Models\Question;
use App\Models\User;
use Database\Factories\UserFactory;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\getJson;

it('should be able to list only published questions', function () {
    $publishedQuestion = Question::factory()->published()->create();
    $draftQuestion = Question::factory()->draft()->create();

    Sanctum::actingAs(User::factory()->create());
    $response = getJson(route('questions.index'))->assertOk();

    $response->assertJsonFragment([
        'id' => $publishedQuestion->id,
        'question' => $publishedQuestion->question,
        'status' => $publishedQuestion->status,
        'created_by' => [
            'id' => $publishedQuestion->user->id,
            'name' => $publishedQuestion->user->name,
            'email' => $publishedQuestion->user->email,
        ],
        'created_at' => $publishedQuestion->created_at->format('Y-m-d H:i:s'),
        'updated_at' => $publishedQuestion->updated_at->format('Y-m-d H:i:s'),
    ])->assertJsonMissing([
                'id' => $draftQuestion->id,
                'question' => $draftQuestion->question,
            ]);
});
