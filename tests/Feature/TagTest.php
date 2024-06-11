<?php

use Symfony\Component\HttpFoundation\Response;

it ('return success when tag is created successfully', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'POST', uri: '/api/tags', data: [
        'name' => 'Tag Test',
    ], headers: [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
    $this->assertDatabaseCount('tags', 1);
});

it ('return error when required attributes is missing', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'POST', uri: '/api/tags',  headers: [
        'Authorization' => 'Bearer ' . $token,
    ]);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                ],
            ],
        ]);
});

it('return success when tag is updated', function () {
    $user = createUserAdmin();
    $tag = \App\Models\Tag::factory()->create()->first();
    $login = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'PUT', uri: "/api/tags/{$tag->id}", data: [
        'name' => 'Tag Test Update'
    ], headers: [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'message' => 'Tag updated successfully',
            ]
        ]);
    $this->assertDatabaseHas('tags', [
        'name' => 'Tag Test Update'
    ]);
});
