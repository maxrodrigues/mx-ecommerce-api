<?php

use App\Models\Offer;
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

it ('should be return success when update an offer', function () {
    $adminUser = createUserAdmin();
    $offer = createOffer();
    $startAt = now()->addDays(5)->format('Y-m-d H:i:s');
    $finishAt = now()->addDays(15)->format('Y-m-d H:i:s');

    $adminLogin = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $adminUser['email'],
        'password' => 'password',
    ]);
    $token = json_decode($adminLogin->content(), true)['data']['token'];
    $response = $this->request(method: 'PUT', uri: 'api/offers/' . $offer->id, data: [
        'discount' => 12,
        'start_at' => $startAt,
        'finish_at' => $finishAt,
    ], headers: [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'message' => 'Offer updated successfully'
            ]
        ]);
    $this->assertDatabaseHas('offers', [
        'id' => $offer->id,
        'code' => $offer->code,
        'discount' => 12,
        'start_at' => $startAt,
        'finish_at' => $finishAt,
    ]);
});

it ('should be return error when offers is not found', function () {
    $adminUser = createUserAdmin();
    $adminLogin = $this->request(method: 'POST', uri: 'api/admin/login', data: [
        'email' => $adminUser['email'],
        'password' => 'password',
    ]);
    $adminToken = json_decode($adminLogin->content(), true)['data']['token'];
    $adminRequest = $this->request(method: 'PUT', uri: 'api/offers/1', headers: [
        'Authorization' => 'Bearer ' . $adminToken
    ]);
    $adminRequest->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => [
                'message' => 'Offer not found'
            ]
        ]);
});
