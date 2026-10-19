<?php

namespace Tests\Feature;

use App\Livewire\Roles\CrudRole;
use App\Livewire\Roles\RolesTable;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolesCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $this->admin = User::factory()->create()->assignRole('Administrador');
    }

    private function permissionIds(string ...$names): array
    {
        return Permission::whereIn('name', $names)->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function test_pages_require_permission(): void
    {
        $cajero = User::factory()->create()->assignRole('Cajero');

        $this->actingAs($cajero)->get('/roles')->assertForbidden();
        $this->actingAs($cajero)->get('/roles/create')->assertForbidden();

        $this->actingAs($this->admin)->get('/roles')->assertOk()->assertSee('Supervisor')->assertSee('Supervisa la operación y al equipo');
        $this->actingAs($this->admin)->get('/roles/create')->assertOk()->assertSee('Visualizar Usuarios');
    }

    public function test_filters_roles_in_use_and_without_permissions(): void
    {
        // Administrador tiene un usuario; Cajero no tiene permisos.
        Livewire::actingAs($this->admin)->test(RolesTable::class)
            ->call('setFilter', 'en_uso')
            ->assertSee('Administrador')
            ->assertDontSee('Atención y operación diaria')
            ->call('setFilter', 'sin_permisos')
            ->assertSee('Atención y operación diaria')
            ->assertDontSee('Control completo del negocio');
    }

    public function test_store_creates_role_with_permissions(): void
    {
        Livewire::actingAs($this->admin)->test(CrudRole::class)
            ->assertSeeHtml('wire:submit="store"')
            ->set('name', 'Encargado')
            ->set('description', 'Turno de la tarde')
            ->set('permission_id', $this->permissionIds('get users', 'edit users'))
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('swalmodal');

        $role = Role::findByName('Encargado');
        $this->assertSame('Turno de la tarde', $role->description);
        $this->assertEqualsCanonicalizing(['get users', 'edit users'], $role->permissions->pluck('name')->all());
    }

    public function test_store_validates_name(): void
    {
        Livewire::actingAs($this->admin)->test(CrudRole::class)
            ->call('store')
            ->assertHasErrors(['name' => 'required'])
            ->set('name', 'Cajero')
            ->call('store')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_update_syncs_permissions(): void
    {
        $role = Role::findByName('Cajero');

        Livewire::actingAs($this->admin)->test(CrudRole::class, ['role' => $role])
            ->assertSeeHtml('wire:submit="update"')
            ->assertSet('name', 'Cajero')
            ->call('toggleGroup', 'Users')
            ->call('update')
            ->assertHasNoErrors();

        $this->assertCount(5, $role->fresh()->permissions);

        Livewire::actingAs($this->admin)->test(CrudRole::class, ['role' => $role->fresh()])
            ->call('toggleGroup', 'Users')
            ->call('update');

        $this->assertCount(0, $role->fresh()->permissions);
    }

    public function test_cannot_edit_own_role_or_administrador(): void
    {
        $this->actingAs($this->admin)->get('/roles/'.Role::findByName('Administrador')->id.'/edit')->assertForbidden();

        Livewire::actingAs($this->admin)->test(RolesTable::class)
            ->call('edit', Role::findByName('Administrador')->id)
            ->assertDispatched('swalmodal')
            ->assertNoRedirect();
    }

    public function test_non_admin_cannot_grant_permissions_they_do_not_have(): void
    {
        $encargado = Role::create(['name' => 'Encargado', 'guard_name' => 'web']);
        $encargado->givePermissionTo(['get roles', 'create roles', 'get users']);
        $user = User::factory()->create()->assignRole('Encargado');

        Livewire::actingAs($user)->test(CrudRole::class)
            ->set('name', 'Nuevo')
            ->set('permission_id', $this->permissionIds('get users', 'delete users'))
            ->call('store')
            ->assertHasErrors('permission_id');

        $this->assertNull(Role::where('name', 'Nuevo')->first());
    }

    public function test_destroy_asks_confirmation_then_deletes(): void
    {
        $role = Role::create(['name' => 'Temporal', 'guard_name' => 'web']);

        Livewire::actingAs($this->admin)->test(RolesTable::class)
            ->call('destroyConfirm', $role->id)
            ->assertDispatched('swalConfirm')
            ->call('destroy', $role->id)
            ->assertDispatched('swalmodal');

        $this->assertNull(Role::find($role->id));
    }

    public function test_cannot_delete_role_with_users_or_administrador(): void
    {
        $cajero = Role::findByName('Cajero');
        User::factory()->create()->assignRole($cajero);

        Livewire::actingAs($this->admin)->test(RolesTable::class)
            ->call('destroy', $cajero->id)
            ->call('destroy', Role::findByName('Administrador')->id);

        $this->assertNotNull(Role::find($cajero->id));
        $this->assertNotNull(Role::findByName('Administrador'));
    }
}
