<?php

namespace App\Livewire;

use Laravel\Fortify\Contracts\UpdatesUserPasswords;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Mi perfil')]
class Profile extends Component
{
    public string $name = '';

    public string $lastname = '';

    public string $username = '';

    public string $email = '';

    /** Muestra u oculta el formulario para cambiar la contraseña */
    public bool $changingPassword = false;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->lastname = auth()->user()->lastname ?? '';
        $this->username = auth()->user()->username ?? '';
        $this->email = auth()->user()->email;
    }

    /**
     * Actualiza nombre, apellido, username y email usando la acción de Fortify (app/Actions/Fortify).
     */
    public function updateProfile(UpdatesUserProfileInformation $updater): void
    {
        $this->resetErrorBag();

        $updater->update(auth()->user(), [
            'name' => $this->name,
            'lastname' => $this->lastname,
            'username' => $this->username,
            'email' => $this->email,
        ]);

        $this->dispatch('swalmodal',
            type: 'success',
            title: __('¡Completado!'),
            text: __('Perfil actualizado.'),
        );
    }

    public function togglePasswordForm(): void
    {
        $this->changingPassword = ! $this->changingPassword;
        $this->reset('current_password', 'password', 'password_confirmation');
        $this->resetErrorBag();
    }

    /**
     * Cambia la contraseña usando la acción de Fortify (app/Actions/Fortify).
     */
    public function updatePassword(UpdatesUserPasswords $updater): void
    {
        $this->resetErrorBag();

        $updater->update(auth()->user(), [
            'current_password' => $this->current_password,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);

        $this->reset('current_password', 'password', 'password_confirmation', 'changingPassword');

        $this->dispatch('swalmodal',
            type: 'success',
            title: __('¡Completado!'),
            text: __('Contraseña actualizada.'),
        );
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
