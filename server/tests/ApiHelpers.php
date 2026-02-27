<?php

namespace Tests;

trait ApiHelpers
{
    protected function login($email, $password)
    {
        return $this->postJson('/api/users/login', [
            'email' => $email,
            'password' => $password,
        ]);
    }

    protected function myGetToken($response)
    {
        // A te Controllered a 'data' kulcson belül adja vissza a user objektumot, 
        // amiben benne van a 'token'
        return $response->json('data.token');
    }

    protected function myGet($uri, $token)
    {
        return $this->withToken($token)->getJson($uri);
    }

    protected function myPost($uri, $data, $token)
    {
        return $this->withToken($token)->postJson($uri, $data);
    }

    protected function myDelete($uri, $token)
    {
        return $this->withToken($token)->deleteJson($uri);
    }

    protected function logout($token)
    {
        return $this->withToken($token)->postJson('/api/users/logout');
    }
}