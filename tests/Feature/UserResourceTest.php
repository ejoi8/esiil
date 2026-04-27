<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the users list page', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ListUsers::class)
        ->assertSuccessful();
});

it('creates a user from the filament resource', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Staff Admin',
            'email' => 'staff@example.test',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'staff@example.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Staff Admin')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('updates a user without changing the password when the password fields are blank', function () {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.test',
        'password' => 'old-password',
    ]);
    $originalHash = $user->password;

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
            'email' => 'updated@example.test',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('updated@example.test')
        ->and($user->password)->toBe($originalHash)
        ->and(Hash::check('old-password', $user->password))->toBeTrue();
});

it('updates a user password from the filament resource', function () {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect(Hash::check('new-secret-password', $user->password))->toBeTrue()
        ->and(Hash::check('old-password', $user->password))->toBeFalse();
});

it('deletes a user from the edit page', function () {
    $this->actingAs(User::factory()->create());

    $user = User::factory()->create();

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(User::query()->whereKey($user->id)->exists())->toBeFalse();
});
