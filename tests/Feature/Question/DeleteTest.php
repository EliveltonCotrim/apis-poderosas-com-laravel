<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->question = $this->user->questions()->create([
        'question' => 'Question Title?',
        'status' => 'draft',
    ]);

    $this->user->questions()->create([
        'question' => 'Question Title 1?',
        'status' => 'draft',
    ]);
});

it('should be able to delete a question', function () {
    Sanctum::actingAs($this->user);

    deleteJson(route('questions.destroy', $this->question))
        ->assertNoContent();

    assertDatabaseCount('questions', 1);
    assertDatabaseMissing('questions', [
        'id' => $this->question->id,
    ]);
});
