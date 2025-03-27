<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->question = $this->user->questions()->create([
        'question' => 'Question Title?',
        'status' => 'draft',
    ]);
});

it('should be able to update a question', function () {

    Sanctum::actingAs($this->user);

    $data = [
        'status' => 'published',
        'question' => 'Question Title 2?',
    ];

    putJson(route('questions.update', $this->question), $data)->assertOk()
        ->assertSessionHasNoErrors()
        ->assertJsonStructure([
            'data' => [
                'id',
                'question',
                'status',
                'created_by' => [
                    'id',
                    'name',
                    'email',
                ],
                'created_at',
                'updated_at',
            ],
        ])->assertSuccessful();

    assertDatabaseCount('questions', 1);
    assertDatabaseHas('questions', $data);

});

describe('validation rules', function(){
    test('question::required', function(){
        Sanctum::actingAs($this->user);

        putJson(route('questions.update', $this->question), [])
            ->assertJsonValidationErrors(['question' => 'The question field is required.']);
    });
});
