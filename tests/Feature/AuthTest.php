<?php

// REGISTER
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

it('should be return error when required attributes is missing on register', function () {
    $response = $this->request('POST', '/api/register');
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

it('should be return user when required attributes is valid and user already exists', function () {

    User::factory()->create([
        'name' => 'Test User',
        'email' => 'w9kCp@example.com',
        'password' => 'password',
    ]);

    $response = $this->request('POST', '/api/register', [
        'name' => 'Test User',
        'email' => 'w9kCp@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => ['The email has already been taken.'],
                ],
            ],
        ]);
});

it('should be return success information when user is created successfully', function (){
    $response = $this->request('POST', '/api/register', [
        'name' => 'Test User',
        'email' => 'w9kCp@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
});


// LOGIN
it('should be return error when required attributes is missing on login', function (){
    $response = $this->request('POST', '/api/login');
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ],
            ]
        ]);

});

it('should be return error when user is not found', function () {
    $user = User::factory()->create();
    $response = $this->request('POST', '/api/login', [
        'email' => $user->email,
        'password' => $user->password . 'wrong',
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJson([
            'data' => [
                'message' => 'These credentials do not match our records.',
            ],
        ]);
});

todo('should be return token when user is authenticated');
