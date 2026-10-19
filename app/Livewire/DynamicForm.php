<?php

namespace App\Livewire;

use Livewire\Component;

class DynamicForm extends Component
{
    public string $modelClass;        // Eloquent model (e.g. User::class)
    public array $fields = [];        // Fields config
    public array $formData = [];      // Submitted data
    public $record = null;            // Existing model instance for edit
    public string $successMessage = '';
    public bool $passwordVisible = false;


    public function mount(string $modelClass, array $fields, $record = null)
    {
        $this->modelClass = $modelClass;
        $this->fields = $fields;
        $this->record = $record;

        // Fill formData (for edit mode)
        foreach ($this->fields as $name => $field) {
            $this->formData[$name] = $record ? $record->$name : '';
        }
    }

    public function rules()
    {
        $rules = [];

        foreach ($this->fields as $name => $field) {
            if (!isset($field['rules'])) {
                continue;
            }

            $rule = $field['rules'];

            // ✅ Handle unique rule for update
            if ($this->record && str_contains($rule, 'unique:')) {
                $parts = explode('|', $rule);
                foreach ($parts as &$part) {
                    if (str_starts_with($part, 'unique:')) {
                        // unique:users,email → unique:users,email,{id}
                        $part .= ',' . $this->record->id;
                    }
                }
                $rule = implode('|', $parts);
            }

            $rules["formData.$name"] = $rule;
        }

        return $rules;
    }

    public function submit()
    {
        $this->validate();

        $modelClass = $this->modelClass;

        if ($this->record) {
            // ✅ Update
            foreach ($this->formData as $key => $value) {
                // Skip empty password updates
                if ($key === 'password' && empty($value)) {
                    continue;
                }
                $this->record->$key = $value;
            }
            $this->record->save();

            $this->successMessage = class_basename($modelClass) . " updated successfully!";
        } else {
            // ✅ Create
            $modelClass::create($this->formData);
            $this->successMessage = class_basename($modelClass) . " created successfully!";
            $this->formData = []; // reset form
        }
    }

    public function render()
    {
        return view('livewire.dynamic-form');
    }
}
