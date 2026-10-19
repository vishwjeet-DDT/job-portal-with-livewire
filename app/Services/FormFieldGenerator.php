<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FormFieldGenerator
{
    /**
     * Generate form fields for any table
     *
     * @param string $tableName
     * @param array $excludeColumns
     * @param array $customFields - Override field configurations
     * @return array
     */
    public function generateFields(
        string $tableName,
        array $excludeColumns = [],
        array $customFields = []
    ): array {
        // Default excluded columns
        $defaultExcluded = ['id', 'remember_token', 'created_at', 'updated_at', 'deleted_at', 'email_verified_at','created_by','updated_by','ip_address'];
        $excludeColumns = array_merge($defaultExcluded, $excludeColumns);

        // Get all columns except excluded ones
        // $columns = collect(Schema::getColumnListing($tableName))
        //     ->reject(fn($col) => in_array($col, $excludeColumns))
        //     ->values()
        //     ->all();

        $modelClass = 'App\\Models\\' . Str::studly(Str::singular($tableName));

        if (class_exists($modelClass)) {
            $model = new $modelClass();

            if (property_exists($model, 'fillable') && !empty($model->getFillable())) {
                $columns = collect($model->getFillable())
                    ->reject(fn($col) => in_array($col, $excludeColumns))
                    ->values()
                    ->all();
            } else {
                $columns = collect(Schema::getColumnListing($tableName))
                    ->reject(fn($col) => in_array($col, $excludeColumns))
                    ->values()
                    ->all();
            }
        } else {
            $columns = collect(Schema::getColumnListing($tableName))
                ->reject(fn($col) => in_array($col, $excludeColumns))
                ->values()
                ->all();
        }

        $fields = [];

        // dd($columns);
        foreach ($columns as $col) {
            // Check if custom field configuration exists
            if (isset($customFields[$col])) {
                $fields[$col] = $customFields[$col];
                continue;
            }

            // Auto-detect field configuration
            $fields[$col] = $this->detectFieldType($tableName, $col);
        }

        // Add special fields (like password confirmation)
        $fields = $this->addSpecialFields($fields);

        return $fields;
    }

    /**
     * Detect field type based on column name and database type
     *
     * @param string $tableName
     * @param string $columnName
     * @return array
     */
    private function detectFieldType(string $tableName, string $columnName): array
    {
        $colType = Schema::getColumnType($tableName, $columnName);
        $inputType = $this->mapColumnToInputType($columnName, $colType);

        return [
            'label' => $this->generateLabel($columnName),
            'type' => $inputType,
            'required' => $this->isRequired($tableName, $columnName),
            'placeholder' => $this->generatePlaceholder($columnName, $inputType),
        ];
    }

    /**
     * Map database column to HTML input type
     *
     * @param string $columnName
     * @param string $colType
     * @return string
     */
    private function mapColumnToInputType(string $columnName, string $colType): string
    {
        // Check column name patterns first
        if (Str::contains($columnName, ['email'])) {
            return 'email';
        }

        if (Str::contains($columnName, ['password'])) {
            return 'password';
        }

        if (Str::contains($columnName, ['phone', 'mobile', 'tel'])) {
            return 'tel';
        }

        if (Str::contains($columnName, ['url', 'website', 'link'])) {
            return 'url';
        }

        if (Str::contains($columnName, ['image', 'photo', 'avatar', 'picture'])) {
            return 'file';
        }

        if (Str::contains($columnName, ['color', 'colour'])) {
            return 'color';
        }

        // Check database type
        switch ($colType) {
            case 'text':
            case 'longtext':
                return 'textarea';

            case 'boolean':
                return 'checkbox';

            case 'integer':
            case 'bigint':
            case 'smallint':
            case 'tinyint':
                return 'select';
            case 'decimal':
            case 'float':
            case 'double':
                return 'number';

            case 'date':
                return 'date';

            case 'datetime':
            case 'timestamp':
                return 'datetime-local';

            case 'time':
                return 'time';

            default:
                return 'text';
        }
    }

    /**
     * Generate human-readable label from column name
     *
     * @param string $columnName
     * @return string
     */
    private function generateLabel(string $columnName): string
    {
        $columnName = preg_replace('/_id$/', '', $columnName); // remove _id only at end
        return Str::title(str_replace(['_', '-'], ' ', $columnName));
    }

    /**
     * Generate placeholder text for input
     *
     * @param string $columnName
     * @param string $inputType
     * @return string
     */
    private function generatePlaceholder(string $columnName, string $inputType): string
    {
        $placeholders = [
            'email' => 'Enter your email address',
            'password' => 'Enter your password',
            'name' => 'Enter your name',
            'first_name' => 'Enter your first name',
            'last_name' => 'Enter your last name',
            'phone' => 'Enter your phone number',
            'address' => 'Enter your address',
            'city' => 'Enter your city',
            'state' => 'Enter your state',
            'country' => 'Enter your country',
            'zip' => 'Enter your zip code',
            'postal_code' => 'Enter your postal code',
        ];

        if (isset($placeholders[$columnName])) {
            return $placeholders[$columnName];
        }

        $label = $this->generateLabel($columnName);

        switch ($inputType) {
            case 'textarea':
                return "Enter {$label}";
            case 'email':
                return 'example@domain.com';
            case 'tel':
                return '+1234567890';
            case 'url':
                return 'https://example.com';
            case 'date':
                return 'YYYY-MM-DD';
            case 'datetime-local':
                return 'Select date and time';
            case 'number':
                return 'Enter a number';
            default:
                return "Enter {$label}";
        }
    }

    /**
     * Check if column is required (not nullable)
     *
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    private function isRequired(string $tableName, string $columnName): bool
    {

        try {
            $matchColumn = ['skill', 'min_salary', 'max_salary'];

            if (in_array($columnName, $matchColumn, true)) {
                return true;
            }
            if (str_ends_with($columnName, '_id')) {
                return true;
            }
             $result = \DB::table('information_schema.COLUMNS')
                ->select('IS_NULLABLE')
                ->where('TABLE_SCHEMA', \DB::getDatabaseName())
                ->where('TABLE_NAME', $tableName)
                ->where('COLUMN_NAME', $columnName)
                ->first();

                if (!$result) {
                    return false;
                }
                if ($result->IS_NULLABLE === 'NO') {
                    return true;
                }
                return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Add special fields like password confirmation
     *
     * @param array $fields
     * @return array
     */
    private function addSpecialFields(array $fields): array
    {
        // Add password confirmation if password field exists
        if (array_key_exists('password', $fields) && !array_key_exists('password_confirmation', $fields)) {
            $fields['password_confirmation'] = [
                'label' => 'Confirm Password',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'Confirm your password',
                'virtual' => true, // Not a real DB column
            ];
        }

        return $fields;
    }

    /**
     * Generate fields for specific common table types
     */
    public function getUserFields(array $customFields = []): array
    {
        return $this->generateFields('users', [], $customFields);
    }

    public function getPostFields(array $customFields = []): array
    {
        return $this->generateFields('posts', [], $customFields);
    }

    public function getProductFields(array $customFields = []): array
    {
        return $this->generateFields('products', [], $customFields);
    }

    /**
     * Generate fields with custom options for select/radio fields
     *
     * @param string $tableName
     * @param array $selectOptions - ['column_name' => ['value' => 'label']]
     * @return array
     */
    public function generateFieldsWithOptions(string $tableName, array $selectOptions = []): array
    {
        $fields = $this->generateFields($tableName);

        foreach ($selectOptions as $columnName => $options) {
            if (isset($fields[$columnName])) {
                $fields[$columnName]['type'] = 'select';
                $fields[$columnName]['options'] = $options;
            }
        }

        return $fields;
    }

    /**
     * Generate fields for relationships (foreign keys)
     *
     * @param string $tableName
     * @param array $relationships - ['foreign_key' => ['table' => 'related_table', 'display' => 'column_to_display']]
     * @return array
     */
    public function generateFieldsWithRelationships(string $tableName, array $relationships = []): array
    {
        $fields = $this->generateFields($tableName);


        foreach ($relationships as $foreignKey => $config) {
            if (isset($fields[$foreignKey])) {
                $relatedTable = $config['table'];
                $displayColumn = $config['display'] ?? 'name';

                // Get options from related table
                $options = \DB::table($relatedTable)
                    ->pluck($displayColumn, 'id')
                    ->toArray();

                $fields[$foreignKey] = [
                    'label' => $this->generateLabel($foreignKey),
                    'type' => 'select',
                    'options' => ['' => '-- Select --'] + $options,
                    'required' => $this->isRequired($tableName, $foreignKey),
                ];
            }
        }

        return $fields;
    }
}
