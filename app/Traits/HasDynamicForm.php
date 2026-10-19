<?php

namespace App\Traits;

use App\Services\FormFieldGenerator;

trait HasDynamicForm
{
    public array $formData = [];
    public array $formFields = [];

    /**
     * Initialize form fields for a specific table
     *
     * @param string $tableName
     * @param array $excludeColumns
     * @param array $customFields
     * @param array $selectOptions
     * @param array $relationships
     */
    public function initializeFormFields(
        string $tableName,
        array $excludeColumns = [],
        array $customFields = [],
        array $selectOptions = [],
        array $relationships = []
    ): void {
        $generator = new FormFieldGenerator();

        // Generate basic fields
        $this->formFields = $generator->generateFields($tableName, $excludeColumns, $customFields);

        // Add select options if provided
        if (!empty($selectOptions)) {
            $this->addSelectOptions($selectOptions);
        }

        // Add relationships if provided
        if (!empty($relationships)) {
            $this->addRelationshipFields($tableName, $relationships);
        }

        // Initialize form data array
        $this->initializeFormData();
    }

    /**
     * Add select options to specific fields
     *
     * @param array $selectOptions
     */
    private function addSelectOptions(array $selectOptions): void
    {
        foreach ($selectOptions as $fieldName => $options) {
            if (isset($this->formFields[$fieldName])) {
                $this->formFields[$fieldName]['type'] = 'select';
                $this->formFields[$fieldName]['options'] = $options;
            }
        }
    }

    /**
     * Add relationship fields (foreign keys as selects)
     *
     * @param string $tableName
     * @param array $relationships
     */
    private function addRelationshipFields(string $tableName, array $relationships): void
    {
        $generator = new FormFieldGenerator();
        $fieldsWithRelationships = $generator->generateFieldsWithRelationships($tableName, $relationships);
        $this->formFields = array_merge($this->formFields, $fieldsWithRelationships);
    }

    /**
     * Initialize form data with default values
     */
    private function initializeFormData(): void
    {
        foreach ($this->formFields as $name => $field) {
            if (!isset($this->formData[$name])) {
                $this->formData[$name] = $this->getDefaultValue($field);
            }
        }
    }

    /**
     * Get default value for a field type
     *
     * @param array $field
     * @return mixed
     */
    private function getDefaultValue(array $field): mixed
    {
        switch ($field['type']) {
            case 'checkbox':
                return false;
            case 'number':
                return $field['default'] ?? null;
            case 'select':
                return $field['default'] ?? '';
            default:
                return $field['default'] ?? '';
        }
    }

    /**
     * Populate form data with existing model data
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    public function populateFormData($model): void
    {

        foreach ($this->formFields as $name => $field) {
            // Skip virtual fields (like password_confirmation)
            if ($field['virtual'] ?? false) {
                continue;
            }

            if ($model->hasAttribute($name)) {
                if($name==='password'){
                    continue;
                }
                $this->formData[$name] = $model->getAttribute($name);
            }
        }
    }

    /**
     * Get validation rules based on form fields
     *
     * @return array
     */
    public function getValidationRules($edit=false): array
    {
        $rules = [];

        foreach ($this->formFields as $name => $field) {
            $fieldRules = [];

            if($edit){
                $fieldRules[] = 'nullable';
            }else if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }


            // Type-specific validation
            switch ($field['type']) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'url':
                    $fieldRules[] = 'url';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    if (isset($field['min'])) $fieldRules[] = "min:{$field['min']}";
                    if (isset($field['max'])) $fieldRules[] = "max:{$field['max']}";
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'datetime-local':
                    $fieldRules[] = 'date';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    if (isset($field['accept'])) {
                        // Convert MIME types to Laravel validation
                        if (str_contains($field['accept'], 'image/')) {
                            $fieldRules[] = 'image';
                        }
                    }
                    break;
                case 'checkbox':
                    $fieldRules[] = 'boolean';
                    break;
            }

            // Password confirmation
            if ($name === 'password') {
                $fieldRules[] = 'min:8';
                if (isset($this->formFields['password_confirmation'])) {
                    $fieldRules[] = 'confirmed';
                }
            }

            if ($name === 'password_confirmation') {
                $fieldRules[] = 'min:8';
            }

            $rules["formData.{$name}"] = implode('|', $fieldRules);
        }

        return $rules;
    }

    /**
     * Get validation messages
     *
     * @return array
     */
    public function getValidationMessages(): array
    {
        $messages = [];

        foreach ($this->formFields as $name => $field) {
            $label = strtolower($field['label']);
            $messages["formData.{$name}.required"] = "The {$label} field is required.";
            $messages["formData.{$name}.email"] = "Please enter a valid email address.";
            $messages["formData.{$name}.numeric"] = "The {$label} must be a number.";
            $messages["formData.{$name}.date"] = "Please enter a valid date.";
            $messages["formData.{$name}.url"] = "Please enter a valid URL.";
        }

        return $messages;
    }
}
