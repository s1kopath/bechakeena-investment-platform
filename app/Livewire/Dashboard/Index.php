<?php

namespace App\Livewire\Dashboard;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
#[Title('Dashboard')]
class Index extends Component
{
    /**
     * Investor dashboard home. Portfolio summary, history, and payouts are built in Phase 5.
     */
    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
