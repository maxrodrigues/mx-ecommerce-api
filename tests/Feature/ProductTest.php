<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\actingAs;

function setUser(): void
{
    $user = User::factory()->create();
    actingAs($user);
}

function createProduct($qtd = 1, $attributes = [])
{
    return Product::factory($qtd)->create($attributes);
}

//LIST
todo ('only registered user can access list of products', function () {
    $response = $this->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = User::factory()->create();
    $this->be($user);
    $response = $this->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_OK);
});

todo ('returns all registered products', function () {
    $user = User::factory()->create();
    $products = Product::factory(10)->create();
    $response = $this->actingAs($user)->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'products' => $products->toArray(),
                'message' => 'Products list retrieved successfully',
            ],
        ]);
});

todo ('should return the details of a product', function () {
    setUser();
    $product = createProduct(1, [
        'sku' => '1234567890123',
    ]);

    $response = $this->request('GET', '/api/product-detail/1234567890123');

    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'product' => $product->first()->toArray(),
                'message' => 'Product retrieved successfully',
            ],
        ]);
});

todo ('should be return error when product not found', function () {
    setUser();
    createProduct(1, [
        'sku' => '1234567890123',
    ]);
    $response = $this->request('GET', '/api/product-detail/1234567890125');
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => [
                'message' => 'Product not found',
            ],
        ]);
});

todo ('should be return products when search by category', function () {
    setUser();
    $category = Category::factory()->create();
    $products = createProduct(2, [
        'category_id' => $category->first()->id,
    ]);

    $response = $this->request('GET', '/api/products-by-category/'.$category->first()->id);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'products' => $products->toArray(),
                'message' => 'Products list retrieved successfully',
            ],
        ]);
});

//STORE
it ('only admin users can create products', function () {
    $response = $this->request(method: 'POST', uri: '/api/products');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = User::factory()->create()->first()->toArray();
    $login = $this->request(method: 'POST', uri: '/api/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'POST', uri: '/api/products', headers: [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it ('return success when receiving all the attributes necessary to register the product', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $category = Category::factory()->create();

    $response = $this->request('POST', '/api/products', [
        'category_id' => $category->first()->id,
        'name' => 'Product Test',
        'description' => 'Product Description',
        'sku' => '1234567890123',
        'price' => 1000,
        'stock' => 100,
    ], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            'data' => [
                'message' => 'Product created successfully',
            ],
        ]);
});

it ('return error when required attributes are not sent', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'POST', uri: '/api/products', headers: [
        'Authorization' => 'Bearer ' . $token,
    ]);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'category_id' => ['The category id field is required.'],
                    'name' => ['The name field is required.'],
                    'description' => ['The description field is required.'],
                    'sku' => ['The sku field is required.'],
                    'price' => ['The price field is required.'],
                    'stock' => ['The stock field is required.'],
                ],
            ],
        ]);
});

it ('return error when trying to register a product already exists', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $product = createProduct();

    $response = $this->request(method: 'POST', uri: '/api/products', data: $product->first()->toArray(), headers: [
        'Authorization' => 'Bearer ' . $token,
    ]);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'sku' => ['The sku has already been taken.'],
                ],
            ],
        ]);
});

//UPDATE
it ('only admin users can update products', function () {
    $response = $this->request(method: 'POST', uri: '/api/products');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = User::factory()->create()->first()->toArray();
    $login = $this->request(method: 'POST', uri: '/api/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'POST', uri: '/api/products', headers: [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it ('return success and product detail when updated successfully', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $product = createProduct();
    $attributes = [
        'name' => 'Product Test Update',
        'description' => 'Product Description Update',
        'sku' => '1234567890123',
        'price' => 99999,
        'stock' => 9,
    ];
    $response = $this->request('PUT', '/api/products/'.$product->first()->sku, $attributes, [
        'Authorization' => 'Bearer ' . $token,
    ]);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'message' => 'Product updated successfully',
                'product' => $attributes,
            ],
        ]);
    $this->assertDatabaseHas('products', $attributes);
});

it ('should return an error when the product is not updated', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $product = createProduct();
    $attributes = [
        'name' => 'Product Test Update',
        'description' => 'Product Description Update',
        'sku' => '1234567890123',
        'price' => 99999,
        'stock' => 9,
    ];
    $response = $this->request('PUT', '/api/products/1234567890125', $attributes, [
        'Authorization' => 'Bearer ' . $token,
    ]);
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => [
                'message' => 'Product not found or not updated',
            ],
        ]);
});

//DELETE
it ('only admin users can delete products', function () {
    $response = $this->request(method: 'POST', uri: '/api/products');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = User::factory()->create()->first()->toArray();
    $login = $this->request(method: 'POST', uri: '/api/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'POST', uri: '/api/products', headers: [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it ('should return success when the product is deleted successfully', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $product = createProduct();
    $response = $this->request('DELETE', '/api/products/'.$product->first()->sku, [], [
        'Authorization' => 'Bearer ' . $token,
    ]);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'message' => 'Product deleted successfully',
            ],
        ]);
    $this->assertDatabaseMissing('products', $product->first()->toArray());
});

it ('should return an error when the product is not deleted', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request('DELETE', '/api/products/1234567890125', [], [
        'Authorization' => 'Bearer ' . $token,
    ]);
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => [
                'message' => 'Product not found or not deleted',
            ],
        ]);
});
