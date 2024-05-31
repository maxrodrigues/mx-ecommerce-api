<?php

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
    return \App\Models\Product::factory($qtd)->create($attributes);
}

//LIST
it('only registered user can access list of products', function () {
    $response = $this->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = User::factory()->create();
    $this->be($user);
    $response = $this->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_OK);
});

it('returns all registered products', function () {
    $user = User::factory()->create();
    $products = \App\Models\Product::factory(10)->create();
    $response = $this->actingAs($user)->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'products' => $products->toArray(),
                'message' => 'Products list retrieved successfully',
            ],
        ]);
});

it('should return the details of a product', function () {
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

it('should be return error when product not found', function () {
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

it('should be return products when search by category', function () {
    setUser();
    $category = \App\Models\Category::factory()->create();
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
it('return success when receiving all the attributes necessary to register the product', function () {
    setUser();
    $category = \App\Models\Category::factory()->create();
    $response = $this->request('POST', '/api/products', [
        'category_id' => $category->first()->id,
        'name' => 'Product Test',
        'description' => 'Product Description',
        'sku' => '1234567890123',
        'price' => 1000,
        'stock' => 100,
    ]);

    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            'data' => [
                'message' => 'Product created successfully',
            ],
        ]);
});

it('return error when required attributes are not sent', function () {
    setUser();
    $response = $this->request('POST', '/api/products');
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

it('return error when trying to register a product already exists', function () {
    setUser();
    $product = createProduct();

    $response = $this->request('POST', '/api/products', $product->first()->toArray());
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
it('return success and product detail when updated successfully', function () {
    setUser();
    $product = createProduct();
    $attributes = [
        'name' => 'Product Test Update',
        'description' => 'Product Description Update',
        'sku' => '1234567890123',
        'price' => 99999,
        'stock' => 9,
    ];
    $response = $this->request('PUT', '/api/products/'.$product->first()->sku, $attributes);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'message' => 'Product updated successfully',
                'product' => $attributes,
            ],
        ]);
    $this->assertDatabaseHas('products', $attributes);
});

it('should return an error when the product is not updated', function () {
    setUser();
    $product = createProduct();
    $attributes = [
        'name' => 'Product Test Update',
        'description' => 'Product Description Update',
        'sku' => '1234567890123',
        'price' => 99999,
        'stock' => 9,
    ];
    $response = $this->request('PUT', '/api/products/1234567890125', $attributes);
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => [
                'message' => 'Product not found or not updated',
            ],
        ]);
});

//DELETE
it('should return success when the product is deleted successfully', function () {
    setUser();
    $product = createProduct();
    $response = $this->request('DELETE', '/api/products/'.$product->first()->sku);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'message' => 'Product deleted successfully',
            ],
        ]);
    $this->assertDatabaseMissing('products', $product->first()->toArray());
});

it('should return an error when the product is not deleted', function () {
    setUser();
    $response = $this->request('DELETE', '/api/products/1234567890125');
    $response->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'data' => [
                'message' => 'Product not found or not deleted',
            ],
        ]);
});
