<?php

it ('should be return success when the stock is updated', function () {
    $product = createProduct();
    $admin = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $admin['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];

    $attributes = [
        'stock' => 9999,
    ];
    $response = $this->request('PUT', '/api/inventory/'.$product->first()->sku, $attributes, [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(\Symfony\Component\HttpFoundation\Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'message' => 'Stock updated successfully'
            ],
        ]);
    $this->assertDatabaseHas('products', $attributes);
});

it ('should be return error when not send stock', function () {
    $product = createProduct();
    $admin = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $admin['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'PUT', uri: '/api/inventory/'.$product->first()->sku, headers: [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(\Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'stock' => ['The stock field is required.']
                ]
            ],
        ]);
});

it ('should be return error when product not found', function () {
    $admin = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $admin['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $attributes = [
        'stock' => 9999,
    ];
    $response = $this->request(method: 'PUT', uri: '/api/inventory/123', data: $attributes, headers: [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(\Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => [
                'message' => 'Product not found'
            ],
        ]);
});
