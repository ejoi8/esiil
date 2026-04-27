<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows authenticated users to access the filament auth panel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/auth')
        ->assertSuccessful();
});
