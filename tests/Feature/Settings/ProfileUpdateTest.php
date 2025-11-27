<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();

    // User should be soft deleted
    expect($user->fresh()->trashed())->toBeTrue();

    // Data should be anonymized
    $deletedUser = User::withTrashed()->find($user->id);
    expect($deletedUser->name)->toStartWith('Deleted User');
    expect($deletedUser->email)->toStartWith('deleted.');
    expect($deletedUser->email)->toEndWith('@deleted.local');
    expect($deletedUser->email_verified_at)->toBeNull();
    expect($deletedUser->remember_token)->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    // User should not be deleted
    expect($user->fresh()->trashed())->toBeFalse();
    expect($user->fresh()->name)->toBe('Test User');
    expect($user->fresh()->email)->toBe('test@example.com');
});

test('social user can delete their account with email confirmation', function () {
    $user = User::factory()->create([
        'name' => 'Social User',
        'email' => 'social@example.com',
    ]);
    $user->socialAccounts()->create([
        'provider' => 'google',
        'provider_id' => '12345',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();

    // User should be soft deleted
    expect($user->fresh()->trashed())->toBeTrue();

    // Data should be anonymized
    $deletedUser = User::withTrashed()->find($user->id);
    expect($deletedUser->name)->toStartWith('Deleted User');
    expect($deletedUser->email)->toStartWith('deleted.');
    expect($deletedUser->email)->toEndWith('@deleted.local');

    // Social accounts should be deleted
    expect($deletedUser->socialAccounts()->count())->toBe(0);
});

test('correct email must be provided for social user to delete account', function () {
    $user = User::factory()->create([
        'name' => 'Social User',
        'email' => 'user@example.com',
    ]);
    $user->socialAccounts()->create([
        'provider' => 'github',
        'provider_id' => '67890',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'email' => 'wrong@example.com',
        ]);

    $response
        ->assertSessionHasErrors('email')
        ->assertRedirect(route('profile.edit'));

    // User should not be deleted
    expect($user->fresh()->trashed())->toBeFalse();
    expect($user->fresh()->name)->toBe('Social User');
    expect($user->fresh()->email)->toBe('user@example.com');
});

test('developer profile is deleted when account is deleted', function () {
    $user = User::factory()->create([
        'role' => 'developer',
        'name' => 'Developer User',
        'email' => 'developer@example.com',
    ]);

    // Create developer profile
    $user->developerProfile()->create([
        'bio' => 'Test bio',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    // User should be soft deleted
    expect($user->fresh()->trashed())->toBeTrue();

    // Developer profile should be deleted
    expect($user->fresh()->developerProfile)->toBeNull();
});
