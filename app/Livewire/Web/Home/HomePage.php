<?php

namespace App\Livewire\Web\Home;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('WhatsApp Cloud Panel')]
class HomePage extends Component
{
    public function render()
    {
        return view('livewire.web.home.home-page');
    }
}
