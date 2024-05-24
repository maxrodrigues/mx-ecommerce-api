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
            'data' => $categories->toArray()
        ]);
});

//it ('return default message when there are no categories registered', function () {
//    $user = User::factory()->create();
//    $response = $this->actingAs($user)->request('GET', '/api/categories');
//    $response->assertStatus(Response::HTTP_OK)
//        ->assertJson([
//            'message' => 'No categories found'
//        ]);
//});
