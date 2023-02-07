<?php

Namespace App\Classes;

use App\Models\User;
use JetBrains\PhpStorm\ArrayShape;

class AuthResponse {
    private string $access_token;
    private User $user;

    public function __construct(User $user, string $access_token)
    {
        $this->user = $user;
        $this->access_token = $access_token;
    }

    #[ArrayShape(['user' => "array", 'token_type' => "string", 'access_token' => "string"])] public function json(): array
    {
        return [
            'user' => $this->user->toArray(),
            'token_type' => 'Bearer',
            'access_token' => $this->access_token,
        ];
    }
}
