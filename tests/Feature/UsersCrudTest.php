<?php

namespace Tests\Feature;

use App\Livewire\Users\CrudUser;
use App\Livewire\Users\UsersTable;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsersCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, PermissionSeeder::class]);
        $this->admin = User::factory()->create()->assignRole('Administrador');
    }

    public function test_pages_require_permission(): void
    {
        $cajero = User::factory()->create()->assignRole('Cajero');

        $this->actingAs($cajero)->get('/users')->assertForbidden();
        $this->actingAs($cajero)->get('/users/create')->assertForbidden();

        $this->actingAs($this->admin)->get('/users')->assertOk()->assertSee('Usuarios');
        $this->actingAs($this->admin)->get('/users/create')->assertOk()->assertSee('Nuevo usuario');
        $this->actingAs($this->admin)->get("/users/{$cajero->id}/edit")->assertOk()->assertSee($cajero->email);
    }

    public function test_store_creates_user_with_role(): void
    {
        $cajero = Role::findByName('Cajero');

        Livewire::actingAs($this->admin)->test(CrudUser::class)
            ->set('name', 'Lucía')
            ->set('lastname', 'Gómez')
            ->set('username', 'lucia_gomez')
            ->set('email', 'lucia@mempanadas.test')
            ->set('password', 'secreto123')
            ->set('role_id', $cajero->id)
            ->call('store')
            ->assertHasNoErrors()
            ->assertDispatched('swalmodal');

        $user = User::where('email', 'lucia@mempanadas.test')->firstOrFail();
        $this->assertSame('Gómez', $user->lastname);
        $this->assertSame('lucia_gomez', $user->username);
        $this->assertTrue($user->hasRole('Cajero'));
        $this->assertTrue(Hash::check('secreto123', $user->password));
    }

    public function test_store_validates_fields(): void
    {
        Livewire::actingAs($this->admin)->test(CrudUser::class)
            ->set('email', $this->admin->email)
            ->set('username', $this->admin->username)
            ->call('store')
            ->assertHasErrors([
                'name' => 'required',
                'lastname' => 'required',
                'username' => 'unique',
                'email' => 'unique',
                'password' => 'required',
                'role_id' => 'required',
            ]);

        Livewire::actingAs($this->admin)->test(CrudUser::class)
            ->set('username', 'con espacios')
            ->call('store')
            ->assertHasErrors(['username' => 'regex']);
    }

    public function test_form_submits_to_the_right_method(): void
    {
        $user = User::factory()->create()->assignRole('Cajero');

        Livewire::actingAs($this->admin)->test(CrudUser::class)
            ->assertSeeHtml('wire:submit="store"');

        Livewire::actingAs($this->admin)->test(CrudUser::class, ['user' => $user])
            ->assertSeeHtml('wire:submit="update"');
    }

    public function test_update_keeps_password_when_blank(): void
    {
        $user = User::factory()->create()->assignRole('Cajero');
        $oldHash = $user->password;

        Livewire::actingAs($this->admin)->test(CrudUser::class, ['user' => $user])
            ->assertSet('action', 'edit')
            ->assertSet('username', $user->username)
            ->set('name', 'Nombre nuevo')
            ->set('role_id', Role::findByName('Supervisor')->id)
            ->call('update')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertSame('Nombre nuevo', $user->name);
        $this->assertSame($oldHash, $user->password);
        $this->assertTrue($user->hasRole('Supervisor'));
    }

    public function test_destroy_asks_confirmation_then_deletes(): void
    {
        $user = User::factory()->create()->assignRole('Cajero');

        Livewire::actingAs($this->admin)->test(UsersTable::class)
            ->call('destroyConfirm', $user->id)
            ->assertDispatched('swalConfirm')
            ->call('destroy', $user->id)
            ->assertDispatched('swalmodal');

        $this->assertModelMissing($user);
    }

    public function test_cannot_delete_self_or_administrators(): void
    {
        $otherAdmin = User::factory()->create()->assignRole('Administrador');

        Livewire::actingAs($this->admin)->test(UsersTable::class)
            ->call('destroy', $this->admin->id)
            ->call('destroy', $otherAdmin->id);

        $this->assertModelExists($this->admin);
        $this->assertModelExists($otherAdmin);
    }

    public function test_user_seeder_creates_an_admin_that_can_see_users(): void
    {
        // Roles y permisos ya se sembraron en setUp().
        $this->seed(UserSeeder::class);

        $admin = User::where('username', 'admin')->firstOrFail();

        $this->assertTrue($admin->hasRole('Administrador'));
        $this->actingAs($admin)->get('/users')->assertOk()->assertSee('supervisor@mempanadas.test');
    }

    public function test_supervisor_cannot_edit_an_administrator(): void
    {
        $supervisor = User::factory()->create()->assignRole('Supervisor');
        $supervisor->givePermissionTo(['get users', 'edit users']);

        $this->actingAs($supervisor)->get("/users/{$this->admin->id}/edit")->assertForbidden();
    }
}
