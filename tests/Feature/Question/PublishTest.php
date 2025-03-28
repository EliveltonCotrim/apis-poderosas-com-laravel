<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use App\Models\Question;

use function Pest\Laravel\{assertDatabaseCount, assertDatabaseHas, deleteJson, putJson};

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

it('should be able to publish a question.', function () {
    Sanctum::actingAs($this->user);

    putJson(route('questions.publish', $this->question))
        ->dump()
        ->assertNoContent();

    assertDatabaseHas('questions', [
        'id' => $this->question->id,
        'status' => 'published',
    ]);
});

it('should allow that only the creator can publish', function () {
    $user2 = User::factory()->create();

    Sanctum::actingAs($user2);

    putJson(route('questions.publish', $this->question))
        ->assertForbidden();

    assertDatabaseCount('questions', 2);
    assertDatabaseHas('questions', [
        'id' => $this->question->id,
        'status' => 'draft',
    ]);
});

it('should only publish when the question is on status draft', function () {

    Sanctum::actingAs($this->user);

    $question =  $this->user->questions()->create([
        'question' => 'Question Title 555?',
        'status' => 'published',
    ]);;

    putJson(route('questions.publish', $question))
        ->assertNotFound();

    assertDatabaseHas('questions', ['id' => $question->id, 'status' => 'published']);

});

it('not should publish with id invalid', function () {

    Sanctum::actingAs($this->user);

    putJson(route('questions.publish', rand(0000000, 9999999)))
        ->assertNotFound();

    assertDatabaseCount('questions', 2);

});
