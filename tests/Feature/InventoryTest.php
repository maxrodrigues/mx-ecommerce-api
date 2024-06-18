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
