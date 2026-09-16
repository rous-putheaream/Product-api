<?php

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists products', function () {
    $products = Product::factory()->count(2)->create();

    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $products[0]->id])
        ->assertJsonFragment(['id' => $products[1]->id]);
});

it('creates a product', function () {
    $response = $this->postJson('/api/products', [
        'name' => 'Desk Lamp',
        'description' => 'A warm reading lamp.',
        'price' => 49.99,
        'stock' => 12,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.name', 'Desk Lamp');

    $this->assertDatabaseHas('products', ['name' => 'Desk Lamp']);
});

it('validates product input', function () {
    $this->postJson('/api/products', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price', 'stock']);
});

it('updates and deletes a product', function () {
    $product = Product::factory()->create();

    $this->patchJson("/api/products/{$product->id}", ['stock' => 4])
        ->assertOk()
        ->assertJsonPath('data.stock', 4);

    $this->deleteJson("/api/products/{$product->id}")
        ->assertNoContent();

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

it('seeds twenty products', function () {
    $this->seed();

    expect(Product::count())->toBe(20);
});
