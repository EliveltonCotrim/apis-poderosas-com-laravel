<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas, assertSoftDeleted, deleteJson};

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

it('should be able to archive a question', function () {
    Sanctum::actingAs($this->user);

    deleteJson(route('questions.archive', $this->question))
        ->assertNoContent();

    assertDatabaseCount('questions', 2);
    assertSoftDeleted('questions', [
        'id' => $this->question->id,
    ]);
});

it('should allow that only the creator can archive', function () {
    $user2 = User::factory()->create();

    Sanctum::actingAs($user2);

    deleteJson(route('questions.archive', $this->question))
        ->assertForbidden();

    assertDatabaseCount('questions', 2);
    assertDatabaseHas('questions', [
        'id' => $this->question->id,
    ]);
});

it('not should archive with id invalid', function () {

    Sanctum::actingAs($this->user);

    deleteJson(route('questions.archive', Str::uuid()))
        ->assertNotFound();

    assertDatabaseCount('questions', 2);

});
