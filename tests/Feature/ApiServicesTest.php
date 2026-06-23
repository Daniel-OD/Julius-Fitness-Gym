uses(RefreshDatabase::class);

function serviceApiUser(): User
{
    $permissions = [
        'ViewAny:Service', 'View:Service', 'Create:Service', 'Update:Service',
        'Delete:Service', 'DeleteAny:Service', 'ForceDelete:Service',
        'ForceDeleteAny:Service', 'Restore:Service', 'RestoreAny:Service',
    ];
    $role = Role::firstOrCreate(['name' => 'services_admin', 'guard_name' => 'web']);
    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }
    $role->syncPermissions($permissions);
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('GET /api/v1/services returns list', function (): void {
    Sanctum::actingAs(serviceApiUser());
    Service::factory()->count(2)->create();

    getJson('/api/v1/services')->assertOk()->assertJsonStructure(['data']);
});

it('POST /api/v1/services creates service', function (): void {
    Sanctum::actingAs(serviceApiUser());

    postJson('/api/v1/services', [
        'name' => 'Cardio Class',
    ])->assertCreated()->assertJsonPath('data.name', 'Cardio Class');
});

it('POST /api/v1/services validates required name', function (): void {
    Sanctum::actingAs(serviceApiUser());

    postJson('/api/v1/services', [])->assertUnprocessable()->assertJsonValidationErrors(['name']);
});

it('GET /api/v1/services/{id} returns service', function (): void {
    Sanctum::actingAs(serviceApiUser());
    $service = Service::factory()->create();

    getJson("/api/v1/services/{$service->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $service->id);
});

it('PATCH /api/v1/services/{id} updates service', function (): void {
    Sanctum::actingAs(serviceApiUser());
    $service = Service::factory()->create(['name' => 'Old']);

    patchJson("/api/v1/services/{$service->id}", ['name' => 'New'])
        ->assertOk()
        ->assertJsonPath('data.name', 'New');
});

it('DELETE /api/v1/services/{id} soft-deletes service', function (): void {
    Sanctum::actingAs(serviceApiUser());
    $service = Service::factory()->create();

    deleteJson("/api/v1/services/{$service->id}")->assertNoContent();

    expect(Service::find($service->id))->toBeNull();
    expect(Service::withTrashed()->find($service->id))->not->toBeNull();
});

it('POST /api/v1/services/{id}/restore restores service', function (): void {
    Sanctum::actingAs(serviceApiUser());
    $service = Service::factory()->create();
    $service->delete();

    postJson("/api/v1/services/{$service->id}/restore")->assertOk();

    expect(Service::find($service->id))->not->toBeNull();
});

it('DELETE /api/v1/services/{id}/force permanently deletes service', function (): void {
    Sanctum::actingAs(serviceApiUser());
    $service = Service::factory()->create();
    $service->delete();

    deleteJson("/api/v1/services/{$service->id}/force")->assertNoContent();

    expect(Service::withTrashed()->find($service->id))->toBeNull();
});

it('services returns 403 without permission', function (): void {
    $role = Role::firstOrCreate(['name' => 'no_perms_svc', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole($role);
    Sanctum::actingAs($user);

    getJson('/api/v1/services')->assertForbidden();
});
