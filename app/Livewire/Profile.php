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

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    /**
     * Actualiza nombre y email usando la acción de Fortify (app/Actions/Fortify).
     */
    public function updateProfile(UpdatesUserProfileInformation $updater): void
    {
        $this->resetErrorBag();

        $updater->update(auth()->user(), [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('profile-status', 'Perfil actualizado.');
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

        $this->reset('current_password', 'password', 'password_confirmation');

        session()->flash('password-status', 'Contraseña actualizada.');
    }

    public function render()
    {
        return view('livewire.profile');
    }
}
