<?php

use App\Models\User;

function user(): ?User
{
    if (auth()->check()) {
        return auth()->user();
    }

    return null;
}
