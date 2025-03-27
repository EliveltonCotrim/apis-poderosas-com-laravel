<?php

use App\Models\{Question, User};
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseEmpty, assertDatabaseHas, postJson};

it('should be create a mew question', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    postJson(route('questions.store'), [
        'status'   => 'draft',
        'question' => 'Question Title?',
    ])->assertCreated()
        ->assertSessionHasNoErrors()
        ->assertJsonStructure([
            'data' => [
                'id',
                'question',
                'status',
                'created_at',
                'updated_at',
            ],
        ])->assertSuccessful();

    assertDatabaseCount('questions', 1);
    assertDatabaseHas('questions', [
        'user_id'  => $user->id,
        'question' => 'Question Title?',
    ]);

});

test('with the creation of the question, we need to make sure that it cretes with _datft_ status', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    postJson(route('questions.store'), [
        'question' => 'Question Title ? ?',
    ])->assertCreated()
        ->assertSessionHasNoErrors()
        ->assertJsonStructure([
            'data' => [
                'id',
                'question',
                'status',
                'created_at',
                'updated_at',
                'created_by' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ])->assertSuccessful();

    assertDatabaseCount('questions', 1);
    assertDatabaseHas('questions', [
        'status'   => 'draft',
        'user_id'  => $user->id,
        'question' => 'Question Title ? ?',
    ]);
});

describe('validation rules', function () {
    test('question is required', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        postJson(route('questions.store'), [])->assertJsonValidationErrors('question');

        assertDatabaseEmpty('questions');
    });

    test('question::ending with question mark', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        postJson(route('questions.store'), [
            'question' => 'Question without question mark',
        ])->assertJsonValidationErrors(['question' => 'O campo question deve terminar com "?"']);

        assertDatabaseEmpty('questions');
    });

    test('question::min caracters should be 10', function () {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        postJson(route('questions.store'), [
            'question' => 'Question?',
        ])->assertJsonValidationErrors(['question' => 'be at least 10']);

        assertDatabaseEmpty('questions');
    });

    test('question::unique in database', function () {
        $user = User::factory()->create();
        Question::factory()->create(['question' => 'Question Title ? ?', 'user_id' => $user->id]);

        Sanctum::actingAs($user);

        postJson(route('questions.store'), [
            'question' => 'Question Title ? ?',
        ])->assertJsonValidationErrors(['question' => 'has already been taken.']);

        assertDatabaseHas('questions', ['question' => 'Question Title ? ?']);
        expect(Question::where('question', 'Question Title ? ?')->count())->toBe(1);
    });
});

test('after creating we we should return a status 201 with the creted question', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    $response = postJson(route('questions.store'), [
        'question' => 'Question Title?',
    ])->assertCreated()
        ->assertSessionHasNoErrors();

    $question = Question::latest()->first()->load('user');

    $response->assertJson([
        'data' => [
            'id'         => $question->id,
            'question'   => $question->question,
            'status'     => $question->status,
            'created_by' => [
                'id'    => $question->user->id,
                'name'  => $question->user->name,
                'email' => $question->user->email,
            ],
            'created_at' => $question->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $question->updated_at->format('Y-m-d H:i:s'),
        ],
    ]);

    assertDatabaseCount('questions', 1);
    assertDatabaseHas('questions', [
        'user_id'  => $user->id,
        'question' => 'Question Title?',
    ]);
});
