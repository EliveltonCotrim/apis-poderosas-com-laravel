<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas, postJson};

it('should be create a mew question', function () {
    $user = User::factory()->create();

    Sanctum::actingAs($user);

    postJson(route('questions.store'), [
        'status' => 'draft',
        'question' => 'Question Title',
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
        'user_id' => $user->id,
        'question' => 'Question Title',
    ]);

});
