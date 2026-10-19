<?php

namespace App\Livewire\IndustryType;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\IndustryType;

class Create extends Component
{
      use HasDynamicForm;

    public function mount()
    {
        $this->initializeFormFields(
            tableName: 'industry_types'
        );
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:industry_types,name',
        ]);
        IndustryType::create($this->formData);
        session()->flash('message', 'Industry Type created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.industry-type.create');
    }
}
