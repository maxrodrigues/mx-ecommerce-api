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
