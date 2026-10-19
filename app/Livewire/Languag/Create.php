<?php

namespace App\Livewire\Languag;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\Language;

class Create extends Component
{
    use HasDynamicForm;

    public function mount()
    {
        $this->initializeFormFields(
            tableName: 'languages',
        );
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:languages,name',
        ]);
        Language::create($this->formData);
        session()->flash('message', 'Language created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }

    public function render()
    {
        return view('livewire.languag.create');
    }
}
