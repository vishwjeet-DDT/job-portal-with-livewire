<?php

namespace App\Livewire;

use Livewire\Component;

class DynamicFields extends Component
{
    public array $fields = [];     // Field definitions
    public array $formData = [];   // Input values

    public function mount(array $fields = [], array $formData = [])
    {
        $this->fields = $fields;

        // Initialize form data (default empty if not provided)
        foreach ($this->fields as $name => $field) {
            $this->formData[$name] = $formData[$name] ?? '';
        }
    }

    public function render()
    {
        return view('livewire.dynamic-fields');
    }
}
