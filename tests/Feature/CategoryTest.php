<?php

use App\Models\Admin;
use App\Models\Category;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

/**
 * @param int $qtd
 * @param array $attributes
 * @return array
 */
function createUserAdmin(int $qtd = 1, array $attributes = []): array
{
    return Admin::factory($qtd)
        ->create($attributes)
        ->first()
        ->toArray();
}

// LIST
it('only admin users can access the list', function () {
    $response = $this->request(method: 'GET', uri: '/api/categories');
    $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];

    $response = $this->request(method: 'GET', uri: '/api/categories', headers: [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(Response::HTTP_OK);
});

it ('return list of categories', function () {
    $user = createUserAdmin();
    $categories = Category::factory(10)->create();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];

    $response = $this->request(method: 'GET', uri: '/api/categories', headers: [
        'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'categories' => $categories->toArray(),
                'message' => 'Categories list retrieved successfully',
            ],
        ]);
});

it ('return default message when there are no categories registered', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);

    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'GET', uri: '/api/categories', headers: [
        'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'categories' => [],
                'message' => 'No categories found',
            ],
        ]);
});

it ('returns all categories linked to the parent category', function () {
    $user = createUserAdmin();
    $parent = Category::factory(1)->create();
    $parentId = $parent->first()->id;
    $categories = Category::factory(4)->create([
        'parent_id' => $parentId,
    ]);
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'GET', uri: "/api/categories/{$parentId}", headers: [
        'Authorization' => 'Bearer ' . $token
    ]);

    $response->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'data' => [
                'categories' => $categories->toArray(),
                'message' => 'Categories list retrieved successfully',
            ],
        ]);
});

//STORE
it ('return success when category is created successfully', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request('POST', '/api/categories', [
        'name' => 'Category Test',
        'description' => 'Category Description',
    ], [
        'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJson([
            'data' => [
                'message' => 'Category created successfully',
            ],
        ]);

    $this->assertDatabaseCount('categories', 1);
});

it ('return error when required attributes is not send', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $response = $this->request(method: 'POST', uri: '/api/categories', headers: [
        'Authorization' => 'Bearer ' . $token
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

it ('returns an error when trying to register a category already registered exists', function () {
    $user = createUserAdmin();
    $login = $this->request(method: 'POST', uri: '/api/admin/login', data: [
        'email' => $user['email'],
        'password' => 'password',
    ]);
    $token = json_decode($login->content(), true)['data']['token'];
    $category = Category::factory()->create();
    $response = $this->request('POST', '/api/categories', [
        'name' => $category->name,
        'description' => 'Category Description',
    ], [
        'Authorization' => 'Bearer ' . $token
    ]);
    $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'data' => [
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name has already been taken.'],
                ],
            ],
        ]);
});
