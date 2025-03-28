<?php

use App\Models\User;
use Illuminate\Support\Str;

use function Pest\Laravel\{assertDatabaseHas, postJson};

beforeEach(function () {
    User::factory()->create([
        'email' => 'teste1@gmail.com',
    ]);
});

it('should be able to register in the application', function () {
    $response = postJson(route('auth.register'), [
        'name'                  => 'Test User',
        'email'                 => 'teste@gmail.com',
        'password'              => 'password',
        'password_confirmation' => 'password',
    ])->assertCreated()->assertSessionDoesntHaveErrors();

    assertDatabaseHas('users', [
        'name'  => 'Test User',
        'email' => 'teste@gmail.com',
    ]);

    expect(Hash::check('password', User::find($response->json()['data']['id'])->password))->toBeTrue();
});

describe('validations', function () {
    test('name', function ($rule, $value, $meta = []) {
        postJson(route('auth.register'), [
            'name'                  => $value,
            'email'                 => 'teste@gmail.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertJsonValidationErrors([
            'name' => __('validation.' . $rule, array_merge(['attribute' => 'name'], $meta)),
        ]);
    })->with([
        'required' => ['required', ''],
        'string'   => ['string', 556],
        'min:3'    => ['min.string', 'ab', ['min' => '3']],
        'max:255'  => ['max.string', Str::random(256), ['max' => '255']],
    ]);

    test('email', function ($rule, $value, $meta = []) {
        postJson(route('auth.register'), [
            'name'                  => 'Test User',
            'email'                 => $value,
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])->assertJsonValidationErrors([
            'email' => __('validation.' . $rule, array_merge(['attribute' => 'email'], $meta)),
        ]);
    })->with([
        'required'           => ['required', ''],
        'string'             => ['string', 556],
        'email'              => ['email', 'abdsadas'],
        'max:255'            => ['max.string', Str::random(256), ['max' => '255']],
        'unique:users,email' => ['unique', 'teste1@gmail.com'],
    ]);
});
