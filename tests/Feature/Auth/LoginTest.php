<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
    ]);
});

it('should be able to login', function () {
    postJson(route('auth.login'), [
        'email' => $this->user->email,
        'password' => 'password',
    ])->assertNoContent()->assertSessionDoesntHaveErrors();

    assertAuthenticatedAs($this->user);
});

describe('validations', function () {
    test('email', function ($rule, $value, $meta = []) {
        postJson(route('auth.login'), [
            'email' => $value,
            'password' => 'password',
        ])->assertJsonValidationErrors([
                    'email' => __('validation.' . $rule, array_merge(['attribute' => 'email'], $meta)),
                ]);
    })->with([
                'required' => ['required', ''],
                'email' => ['email', 'abdsadas'],
            ]);

    test('password', function ($rule, $value, $meta = []) {
        postJson(route('auth.login'), [
            'email' => 'teste@gmail.com',
            'password' => $value,
        ])->assertJsonValidationErrors([
                    'password' => __('validation.' . $rule, array_merge(['attribute' => 'password'], $meta))
                ]);
    })->with([
                'required' => ['required', ''],
                'min:6' => ['min.string', 'sd', ['min' => '6']],
            ]);
});


