<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
    ]);
});

it('should be able to logout', function () {
    $user = User::factory()->create([
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
    ]);

    actingAs($user);

    postJson(route('auth.logout'))->assertNoContent();

    assertGuest();
});

