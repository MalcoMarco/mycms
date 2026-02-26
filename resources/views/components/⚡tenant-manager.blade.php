<?php

use Livewire\Component;
use App\Models\Tenant;

new class extends Component
{
  public Tenant $tenant;

    public function mount(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }
};
?>

<div>
    {{-- When there is no desire, all things are at peace. - Laozi --}}
    <h1 class="text-xl font-bold">Manager a Tenant: {{ $tenant->id }}</h1>
</div>