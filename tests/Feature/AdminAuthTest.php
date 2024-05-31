<?php

use Symfony\Component\HttpFoundation\Response;

it ('should be return success when create new admin user', function () {
    $response = $this->request('POST', '/api/admin/register', [
        'name' => 'Test Admin User',
        'email' => 'admin@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
});

it ('should be return error when required attributes is missing on register', function () {
    $response = $this->request('POST', '/api/admin/register');
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    $response->assertJson([
        'data' => [
            'message' => 'The given data was invalid.',
            'errors' => [
                'name' => ['The name field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ],
        ],
    ]);
});

it ('should be return success when admin sign in successfully', function () {
    $admin = \App\Models\Admin::factory()->create();
    $response = $this->request('POST', '/api/admin/login', [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_OK);
});
