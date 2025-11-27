<?php

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register as developer', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test Developer',
        'email' => 'developer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'developer',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    expect(auth()->user())
        ->role->toBe('developer')
        ->isDeveloper()->toBeTrue();
});

test('new users can register as hr', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test HR',
        'email' => 'hr@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'hr',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    expect(auth()->user())
        ->role->toBe('hr')
        ->isHR()->toBeTrue();
});

test('registration requires role field', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});

test('registration rejects invalid role', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'invalid-role',
    ]);

    $response->assertSessionHasErrors('role');
    $this->assertGuest();
});
