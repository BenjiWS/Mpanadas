<?php

namespace Tests\Feature;

use App\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_login_and_register_screens_render(): void
    {
        $this->get('/login')->assertOk()->assertSee('Iniciar sesión');
        $this->get('/register')->assertOk()->assertSee('Crear cuenta');
        $this->get('/forgot-password')->assertOk()->assertSee('Recuperar contraseña');
    }

    public function test_users_can_register(): void
    {
        $this->post('/register', [
            'name' => 'Ana',
            'lastname' => 'Pérez',
            'username' => 'anaperez',
            'email' => 'ana@example.com',
            'password' => 'password-segura-123',
            'password_confirmation' => 'password-segura-123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ana@example.com', 'username' => 'anaperez']);
    }

    public function test_users_can_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_dashboard_shows_the_logged_in_user(): void
    {
        $user = User::factory()->create(['name' => 'Benjamin Rosales']);

        $this->actingAs($user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Benjamin Rosales')
            ->assertSee('BR');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('name', 'Nombre Nuevo')
            ->set('email', 'nuevo@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $this->assertSame('Nombre Nuevo', $user->fresh()->name);
        $this->assertSame('nuevo@example.com', $user->fresh()->email);
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('current_password', 'password')
            ->set('password', 'nueva-password-123')
            ->set('password_confirmation', 'nueva-password-123')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('password', '');

        $this->assertTrue(Hash::check('nueva-password-123', $user->fresh()->password));
    }

    public function test_password_update_requires_the_current_password(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(Profile::class)
            ->set('current_password', 'incorrecta')
            ->set('password', 'nueva-password-123')
            ->set('password_confirmation', 'nueva-password-123')
            ->call('updatePassword')
            ->assertHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }
}
