<?php

use App\Models\Category;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

it ('only registered user can view list of categories', function () {
    $response = $this->request('GET', '/api/categories');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = User::factory()->create();
    $response = $this->actingAs($user)->request('GET', '/api/categories');
    $response->assertStatus(Response::HTTP_OK);
});

it ('return list of categories', function () {
    $user = User::factory()->create();
    $categories = Category::factory(10)->create();
    $response = $this->actingAs($user)->request('GET', '/api/categories');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'categories' => $categories->toArray(),
                'message' => 'Categories list retrieved successfully',
            ]
        ]);
});

it ('return default message when there are no categories registered', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->request('GET', '/api/categories');
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'categories' => [],
                'message' => 'No categories found'
            ],
        ]);
});

it ('return success when category is created successfully', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->request('POST', '/api/categories', [
        'name' => 'Category Test',
        'description' => 'Category Description',
    ]);
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            'data' => [
                'message' => 'Category created successfully',
            ]
        ]);

    $this->assertDatabaseCount('categories', 1);
});

it ('return error when required attributes is not send', function () {
    $user = User::factory()->create();
    $response = $this->actingAs($user)->request('POST', '/api/categories');
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                ]
            ],
        ]);
});
