<?php

namespace App\Livewire\FunctionalArea;

use Livewire\Component;
use App\Traits\HasDynamicForm;
use App\Models\{FuncationalArea,Industry};
class Edit extends Component
{

    use HasDynamicForm;

    public $funcational_area;
    public $funcational_area_id;

    protected $listeners = ['setData'];

    public function mount($funcational_area = null, $funcational_area_id = null)
    {
        $this->funcational_area = $funcational_area;

        if ($funcational_area) {
            $this->loadFuncationalArea($funcational_area);
        }

        $this->initializeFormFields(
            tableName: 'funcational_areas',
            selectOptions: [
                'industry_id' => Industry::get()->pluck('name', 'id')->toArray()
            ]
        );

        if ($this->funcational_area) {
            $this->formData = $this->funcational_area->toArray();
        }
    }

    public function setData($data)
    {
        $id = $data['id'] ?? null;

        if ($id) {
            $this->loadFuncationalArea($id);
            $this->dispatch('modal-show', id: 'editModal');
        }
    }

    public function loadFuncationalArea($id)
    {
        $funcational_area = FuncationalArea::findOrFail($id);
        $this->funcational_area = $funcational_area;
        $this->formData = $funcational_area->toArray();
    }

    public function save()
    {
        if (!$this->funcational_area) {
            session()->flash('error', 'No funcational_area selected for update.');
            return;
        }
        $this->validate([
            'formData.name' => 'required|string|max:255|unique:roles,name,' . $this->funcational_area->id,
            'formData.industry_id' => 'required',
        ]);

        $this->funcational_area->update($this->formData);
        session()->flash('message', 'Funcational Area updated successfully!');
        $this->dispatch('modal-hide', id: 'editModal');
        $this->dispatch('refreshTable', id: 'dataTable');
    }
    public function render()
    {
        return view('livewire.functional-area.edit');
    }
}
