<?php

use App\Models\{Question, User};
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas, assertDatabaseMissing, putJson};

beforeEach(function () {
    $this->user     = User::factory()->create();
    $this->question = $this->user->questions()->create([
        'question' => 'Question Title?',
        'status'   => 'draft',
    ]);

    $this->user->questions()->create([
        'question' => 'Question Title 2?',
        'status'   => 'draft',
    ]);
});

it('should be able to update a question', function () {

    Sanctum::actingAs($this->user);

    $data = [
        'status'   => 'published',
        'question' => 'Question Title 1?',
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

    assertDatabaseCount('questions', 2);
    assertDatabaseHas('questions', $data);

});

describe('validation rules', function () {
    test('question::required', function () {
        Sanctum::actingAs($this->user);

        putJson(route('questions.update', $this->question), [])
            ->assertJsonValidationErrors(['question' => 'The question field is required.']);
    });

    test('question::min caracters should be 10', function () {
        Sanctum::actingAs($this->user);

        putJson(route('questions.update', $this->question), [
            'question' => 'Question?',
        ])
            ->assertJsonValidationErrors(['question' => 'be at least 10']);
    });

    test('question::ending with question mark', function () {
        Sanctum::actingAs($this->user);

        putJson(route('questions.update', $this->question), [
            'question' => 'Question without question mark',
        ])->assertJsonValidationErrors(['question' => 'O campo question deve terminar com "?"']);
    });

    test('question::unique in database', function () {
        Sanctum::actingAs($this->user);

        putJson(route('questions.update', $this->question), [
            'question' => 'Question Title 2?',
        ])->assertJsonValidationErrors(['question' => 'has already been taken.']);

        assertDatabaseHas('questions', ['question' => 'Question Title 2?']);
        expect(Question::where('question', 'Question Title 2?')->count())->toBe(1);
    });

    test('question::Should allow updating a question with the same content', function () {
        Sanctum::actingAs($this->user);

        putJson(route('questions.update', $this->question), [
            'question' => $this->question->question,
        ])->assertOk()->assertJsonMissingValidationErrors('question');

        assertDatabaseHas('questions', ['question' => $this->question->question]);
        expect(Question::where('question', $this->question->question)->count())->toBe(1);
    });

    test('question::should be able to edit only if the status is in draft', function () {
        Sanctum::actingAs($this->user);

        $question = $this->user->questions()->create([
            'question' => 'Question Title 3?',
            'status'   => 'published',
        ]);

        putJson(route('questions.update', $question), [
            'question' => 'Question Title 3?',
            'status'   => 'published',
        ])->assertJsonValidationErrors(['question' => 'The question should be a draft to be able to edit.']);
    });
});

describe('security', function () {
    test('only the person who create the question can update the same question', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $question = $user1->questions()->create([
            'question' => 'Question Title?',
            'status'   => 'draft',
        ]);

        Sanctum::actingAs($user2);

        putJson(route('questions.update', $question), [
            'question' => 'Question Title 5?',
        ])->assertForbidden();

        assertDatabaseCount('questions', 3);
        assertDatabaseMissing('questions', [
            'question' => 'Question Title 5?',
            'user_id'  => $user1->id,
        ]);
    });
});

test('after updated we we should return a status 200 with the creted question', function () {

    Sanctum::actingAs($this->user);

    $response = putJson(route('questions.update', $this->question), [
        'question' => 'Question Title 355?',
    ])->assertOk()
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

    assertDatabaseCount('questions', 2);
    assertDatabaseHas('questions', [
        'user_id'  => $this->user->id,
        'question' => 'Question Title 355?',
    ]);
});
