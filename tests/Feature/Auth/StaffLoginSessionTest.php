<?php

use App\Enums\Status;
use App\Models\User;
use Spatie\Permission\Models\Role;

test('an already-authenticated admin revisiting /staff/login stays logged in', function (): void {
    $user = User::factory()->create([
        'status' => Status::Active,
        'email_verified_at' => now(),
    ]);
    $role = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $user->assignRole($role);

    $this->actingAs($user)
        ->withSession(['filament.authenticated_panel_id' => 'admin'])
        ->get('/staff/login')
        ->assertRedirect('/admin');

    expect(auth()->check())->toBeTrue();
});
