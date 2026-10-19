<?php

namespace App\Livewire\Email;

use App\Models\EmailTemplate;
use Livewire\Component;
use App\Traits\HasDynamicForm;

class Create extends Component
{
    use HasDynamicForm;

    public function mount()
    {
        $this->initializeFormFields(
            tableName: 'email_templates'
        );
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:email_templates,name',
            'formData.subject' => 'required|string',
            'formData.body' => 'required|string',
        ]);
        EmailTemplate::create($this->formData);
        session()->flash('message', 'Email Template created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.email.create');
    }
}
