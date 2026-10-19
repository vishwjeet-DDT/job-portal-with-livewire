<?php

namespace App\Livewire\Industry;

use App\Models\IndustryType;
use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\Industry;

class Create extends Component
{
    use HasDynamicForm;

    public function mount()
    {
        $this->initializeFormFields(
            tableName: 'industries',
            selectOptions: [
            'industry_types_id' => IndustryType::pluck('name', 'id')->toArray(),
        ]);
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:industries,name',
        ]);
        Industry::create($this->formData);
        session()->flash('message', 'Industry created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.industry.create');
    }
}
