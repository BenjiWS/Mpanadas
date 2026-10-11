<?php

namespace App\Livewire;

use Livewire\Component;

class Contador extends Component
{
    public int $count = 0;

    public function incrementar(): void
    {
        $this->count++;
    }

    public function render()
    {
        return view('livewire.contador');
    }
}
