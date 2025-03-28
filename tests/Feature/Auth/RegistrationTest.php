<?php

use App\Models\User;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

it('should be able to register in the application', function () {
    $response = postJson(route('auth.register'), [
        'name' => 'Test User',
        'email' => 'teste@gmail.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertCreated()->assertSessionDoesntHaveErrors();

    assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'teste@gmail.com',
    ]);

    expect(Hash::check('password', User::find($response->json()['data']['id'])->password))->toBeTrue();
});
