<?php

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use function Pest\Laravel\actingAs;

function setUser(): void
{
    $user = User::factory()->create();
    actingAs($user);
}

it ('only registered user can access list of products', function () {
    $response = $this->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = User::factory()->create();
    $this->be($user);
    $response = $this->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_OK);
});

it ('returns all registered products', function () {
    $user = User::factory()->create();
    $products = \App\Models\Product::factory(10)->create();
    $response = $this->actingAs($user)->request('GET', '/api/products');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'products' => $products->toArray(),
                'message' => 'Products list retrieved successfully',
            ]
        ]);
});

todo ('should return the details of a product');
todo ('should be must search for products by name');
todo ('should be must search for products by ean');
todo ('should be return products when search by category');
todo ('return success when receiving all the attributes necessary to register the product');
todo ('return error when required attributes are not sent');
todo ('return error when trying to register a product already registered exists');
todo ('return success and product detail when updated successfully');
todo ('should return an error when the product is not updated');
todo ('should return success when the product is deleted successfully');
todo ('should return an error when the product is not deleted');
todo ('should return an error when the product does not exist');
todo ('should return an error when the product is not found');
