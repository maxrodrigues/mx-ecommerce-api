<?php

use Symfony\Component\HttpFoundation\Response;

it ('only admin users can create offers', function () {
    $adminUser = createUserAdmin();
    $user = createUser();

    $userLogin = $this->request(method: 'POST', uri: 'api/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);
    $userToken = json_decode($userLogin->content(), true)['data']['token'];
    $userRequest = $this->request(method: 'POST', uri: 'api/offers', headers: [
        'Authorization' => 'Bearer ' . $userToken
    ]);
    $userRequest->assertStatus(Response::HTTP_UNAUTHORIZED);

    $adminLogin = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $adminUser['email'],
        'password' => 'password',
    ]);
    $adminToken = json_decode($adminLogin->content(), true)['data']['token'];
    $adminRequest = $this->request(method: 'POST', uri: 'api/offers', data: [
        'name' => 'Test offer',
        'code' => 'testoffer',
        'discount' => 10,
        'start_at' => now(),
        'finish_at' => now()->addDays(10),
    ], headers: [
        'Authorization' => 'Bearer ' . $adminToken
    ]);
    $adminRequest->assertStatus(Response::HTTP_CREATED);
});

it ('should be return success when store an offer', function () {
    $adminUser = createUserAdmin();
    $adminLogin = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $adminUser['email'],
        'password' => 'password',
    ]);
    $adminToken = json_decode($adminLogin->content(), true)['data']['token'];
    $adminRequest = $this->request(method: 'POST', uri: 'api/offers', data: [
        'name' => 'Test offer',
        'code' => 'testoffer',
        'discount' => 10,
        'start_at' => now(),
        'finish_at' => now()->addDays(10),
    ], headers: [
        'Authorization' => 'Bearer ' . $adminToken
    ]);
    $adminRequest->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            'data' => [
                'message' => 'Offer created successfully'
            ]
        ]);
    $this->assertDatabaseCount('offers', 1);
});

it ('should be return error when required parameter is missing', function () {
    $adminUser = createUserAdmin();
    $adminLogin = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $adminUser['email'],
        'password' => 'password',
    ]);
    $adminToken = json_decode($adminLogin->content(), true)['data']['token'];
    $adminRequest = $this->request(method: 'POST', uri: 'api/offers', headers: [
        'Authorization' => 'Bearer ' . $adminToken
    ]);
    $adminRequest->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name field is required.'
                    ],
                    'code' => [
                        'The code field is required.'
                    ],
                    'discount' => [
                        'The discount field is required.'
                    ],
                    'start_at' => [
                        'The start at field is required.'
                    ],
                    'finish_at' => [
                        'The finish at field is required.'
                    ]
                ]
            ],
        ]);
});
