<?php

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\putJson;

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

it('should be able to restore a question', function () {
    Sanctum::actingAs($this->user);
    $this->question->delete();

    assertSoftDeleted('questions', [
        'id' => $this->question->id,
    ]);

    putJson(route('questions.restore', $this->question))
        ->assertOk();

    assertDatabaseCount('questions', 2);
    assertNotSoftDeleted('questions', [
        'id' => $this->question->id,
    ]);
});

it('should allow that only the creator can restore', function () {
    $user2 = User::factory()->create();
    $this->question->delete();

    Sanctum::actingAs($user2);

    putJson(route('questions.restore', $this->question))
        ->assertForbidden();

    assertDatabaseCount('questions', 2);
    assertSoftDeleted('questions', [
        'id' => $this->question->id,
    ]);
});

it('not should restore with id invalid', function () {

    Sanctum::actingAs($this->user);
    $this->question->delete();

    putJson(route('questions.restore', rand(000000, 999999)))
        ->assertNotFound();

    assertDatabaseCount('questions', 2);
    assertSoftDeleted('questions', [
        'id' => $this->question->id,
    ]);

});

