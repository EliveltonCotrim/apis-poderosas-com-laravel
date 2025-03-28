<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas, assertDatabaseMissing, deleteJson};

beforeEach(function () {
    $this->user     = User::factory()->create();
    $this->question = $this->user->questions()->create([
        'question' => 'Question Title?',
        'status'   => 'draft',
    ]);

    $this->user->questions()->create([
        'question' => 'Question Title 1?',
        'status'   => 'draft',
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

it('should allow that only the creator can delete', function () {
    $user2 = User::factory()->create();

    Sanctum::actingAs($user2);

    deleteJson(route('questions.destroy', $this->question))
        ->assertForbidden();

    assertDatabaseCount('questions', 2);
    assertDatabaseHas('questions', [
        'id' => $this->question->id,
    ]);
});

it('not should delete with id invalid', function () {

    Sanctum::actingAs($this->user);

    deleteJson(route('questions.destroy', Str::uuid()))
        ->assertNotFound();

    assertDatabaseCount('questions', 2);

});
