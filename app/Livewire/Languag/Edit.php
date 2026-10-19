<?php

namespace App\Livewire\Languag;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\Language;

class Edit extends Component
{
    use HasDynamicForm;
     public $language;
    public $language_id;

    protected $listeners = ['setData'];

    public function mount($language = null, $language_id = null)
    {
        $this->language = $language;
        $this->initializeFormFields(
            tableName: 'languages'
        );

        if ($this->language) {
            $this->formData = $this->language->toArray();
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
        $data = Language::findOrFail($id);
        $this->language = $data;
        $this->formData = $data->toArray();
    }

    public function save()
    {
        // Ensure we have a role to update
        if (!$this->language) {
            session()->flash('error', 'No language selected for update.');
            return;
        }

        $this->validate([
            'formData.name' => 'required|string|max:255|unique:languages,name,' . $this->language->id,
        ]);

        $this->language->update($this->formData);
        session()->flash('message', 'Language updated successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.languag.edit');
    }
}
