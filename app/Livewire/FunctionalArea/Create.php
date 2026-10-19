<?php

namespace App\Livewire\FunctionalArea;

use App\Models\FuncationalArea;
use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\Industry;

class Create extends Component
{

    use HasDynamicForm;

    public function mount()
    {
        $this->initializeFormFields(
            tableName: 'funcational_areas',
            selectOptions: [
                'industry_id' => Industry::get()->pluck('name', 'id')->toArray()
            ]
        );
    }

    public function save()
    {
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:funcational_areas,name',
            'formData.industry_id' => 'required',
        ]);
        FuncationalArea::create($this->formData);
        session()->flash('message', 'Functional Area created successfully!');
        $this->reset('formData');
        $this->dispatch('modal-hide', id: 'createModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.functional-area.create');
    }
}
