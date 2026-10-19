<?php

namespace App\Livewire\Email;

use App\Models\EmailTemplate;
use Livewire\Component;
use App\Traits\HasDynamicForm;

class Edit extends Component
{
      use HasDynamicForm;

    public $email_template;
    public $email_template_id;

    protected $listeners = ['setData'];

    public function mount($email_template = null, $email_template_id = null)
    {
        $this->initializeFormFields(
            tableName: 'email_templates',
        );

        if ($this->email_template) {
            $this->formData = $this->email_template->toArray();
        }
    }

    public function setData($data)
    {
        $id = $data['id'] ?? null;

        if ($id) {
            $this->loadData($id);
            $this->dispatch('modal-show', id: 'editModal');
        }
    }

    public function loadData($id)
    {
        $data = EmailTemplate::findOrFail($id);
        $this->email_template = $data;
        $this->formData = $data->toArray();
    }

    public function save()
    {

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:email_templates,name,' . $this->email_template->id,
            'formData.subject' => 'required|string',
            'formData.body' => 'required|string',
        ]);

        $this->email_template->update($this->formData);
        session()->flash('message', 'email template updated successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.email.edit');
    }
}
