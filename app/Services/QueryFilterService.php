<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Contracts\FilterableInterface;
use App\Models\SavedFilter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class QueryFilterService
{
    protected array $globalFilters = [];
    protected array $relationshipFilters = [];
    protected array $customFilters = [];

    public function handle(Request $request, EloquentBuilder|QueryBuilder $query, array $options = [])
    {
        $filterContext = new FilterContext($request, $options);

        // Count total records before applying filters
        $modelClass = $options['model'] ?? $this->getModelFromQuery($query);

        if ($modelClass) {
            $allRequestedColumns = array_merge(
                array_keys($filterContext->getFieldFilters()),
                $filterContext->getSearchableColumns(),
                // array_keys($filterContext->getSorts())
            );
            $this->validateColumnsExist($allRequestedColumns, $modelClass);
        }
        $totalRecords = $query->count();

        // Apply all filters
        $query = $this->applyAllFilters($query, $filterContext);

        // Count filtered records
        $filteredRecords = $query->count();

        // Apply sorting
        $query = $this->applySorting($query, $filterContext->getSorts(),$request);

        return $query;
        // Handle pagination/results
        return $this->formatResponse($query, $filterContext, $totalRecords, $filteredRecords);
    }

    protected function applyAllFilters(EloquentBuilder|QueryBuilder $query, FilterContext $context): EloquentBuilder|QueryBuilder
    {
        // Apply saved filters first
        // $query = $this->applySavedFilters($query, $context);

        // Apply filter groups (complex conditions)
        $query = $this->applyFilterGroups($query, $context);


        // Apply global search
        $query = $this->applyGlobalSearch($query, $context);

        // Apply individual field filters
        // dd($context->getFieldFilters());
        $query = $this->applyFieldFilters($query, $context);
        // Apply relationship filters
        $query = $this->applyRelationshipFilters($query, $context);

        // Apply custom filters
        $query = $this->applyCustomFilters($query, $context);

        return $query;
    }

    // protected function applySavedFilters(EloquentBuilder|QueryBuilder $query, FilterContext $context): EloquentBuilder|QueryBuilder
    // {
    //     if (!$context->getRequest()->filled('filter_id')) {
    //         return $query;
    //     }

    //     $savedFilter = SavedFilter::find($context->getRequest()->filter_id);
    //     if (!$savedFilter) {
    //         return $query;
    //     }

    //     $filterGroup = [
    //         'logic' => $savedFilter->match ?? 'and',
    //         'conditions' => $savedFilter->conditions ?? [],
    //     ];

    //     return $this->applyFilterGroup($query, $filterGroup);
    // }

    protected function applyFilterGroups(EloquentBuilder|QueryBuilder $query, FilterContext $context): EloquentBuilder|QueryBuilder
    {
        $filterGroup = $context->getFilterGroup();
        if (!$filterGroup) {
            return $query;
        }

        return $query->where(function ($q) use ($filterGroup) {
            $this->applyFilterGroup($q, $filterGroup);
        });
    }

    protected function applyFilterGroup($query, array $group, string $parentLogic = 'and'): void
    {
        $logic = strtolower($group['logic'] ?? 'and');
        $method = $parentLogic === 'or' ? 'orWhere' : 'where';

        $query->$method(function ($q) use ($group, $logic) {
            foreach ($group['conditions'] as $condition) {
                if (isset($condition['field'], $condition['operator'], $condition['value'])) {
                    $this->applyCondition($q, $condition, $logic);
                } elseif (isset($condition['conditions'])) {
                    $this->applyFilterGroup($q, $condition, $logic);
                }
            }
        });
    }

    protected function applyCondition($query, array $condition, string $logic): void
    {
        $field = $condition['field'];
        $operator = $this->mapOperator($condition['operator']);
        $value = $this->prepareValue($condition['value'], $operator);

        $method = $logic === 'or' ? 'orWhere' : 'where';

        // Handle special operators
        if ($operator === 'in') {
            $query->$method(function ($q) use ($field, $value, $logic) {
                $method = $logic === 'or' ? 'orWhereIn' : 'whereIn';
                $q->$method($field, is_array($value) ? $value : explode(',', $value));
            });
        } elseif ($operator === 'not_in') {
            $query->$method(function ($q) use ($field, $value, $logic) {
                $method = $logic === 'or' ? 'orWhereNotIn' : 'whereNotIn';
                $q->$method($field, is_array($value) ? $value : explode(',', $value));
            });
        } elseif ($operator === 'between') {
            $values = is_array($value) ? $value : explode(',', $value);
            if (count($values) === 2) {
                $method = $logic === 'or' ? 'orWhereBetween' : 'whereBetween';
                $query->$method($field, $values);
            }
        } elseif ($operator === 'null') {
            $method = $logic === 'or' ? 'orWhereNull' : 'whereNull';
            $query->$method($field);
        } elseif ($operator === 'not_null') {
            $method = $logic === 'or' ? 'orWhereNotNull' : 'whereNotNull';
            $query->$method($field);
        } else {
            $query->$method($field, $operator, $value);
        }
    }

    protected function applyGlobalSearch(EloquentBuilder|QueryBuilder $query, FilterContext $context): EloquentBuilder|QueryBuilder
    {
        $searchTerm = $context->getSearchTerm();
        if (!$searchTerm) {
            return $query;
        }

        $searchableColumns = $context->getSearchableColumns();
        if (empty($searchableColumns)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchTerm, $searchableColumns) {
            foreach ($searchableColumns as $field => $column) {
                if ($this->shouldSkipFilter($field, $column)) {
                    continue;
                }
                if (str_contains($column, '.')) {
                    // Handle relationship searches
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($subQ) use ($field, $searchTerm) {
                        $subQ->where($field, 'like', "%{$searchTerm}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$searchTerm}%");
                }
            }
        });
    }

    protected function applyFieldFilters(EloquentBuilder|QueryBuilder $query, FilterContext $context): EloquentBuilder|QueryBuilder
    {
        $filters = $context->getFieldFilters();

        // dd($filters);
        foreach ($filters as $field => $value) {
            if ($this->shouldSkipFilter($field, $value)) {
                continue;
            }

            // Check if it's a custom filter method
            if (method_exists($this, "filter" . ucfirst(camelCase($field)))) {
                $method = "filter" . ucfirst(camelCase($field));
                $this->$method($query, $value);
                continue;
            }

            // Handle different value types
            if (is_array($value)) {
                if (count($value) === 2 && $this->isRangeFilter($field)) {
                    $query->whereBetween($field, $value);
                } else {
                    $query->whereIn($field, $value);
                }
            } else {
                $this->applySimpleFilter($query, $field, $value);
            }
        }

        return $query;
    }

    protected function applyRelationshipFilters(EloquentBuilder|QueryBuilder $query, FilterContext $context): EloquentBuilder|QueryBuilder
    {
        $relationFilters = $context->getRelationshipFilters();

        foreach ($relationFilters as $relation => $conditions) {
            $query->whereHas($relation, function ($q) use ($conditions) {
                foreach ($conditions as $field => $value) {
                    if (is_array($value)) {
                        $q->whereIn($field, $value);
                    } else {
                        $q->where($field, $value);
                    }
                }
            });
        }

        return $query;
    }

    protected function applyCustomFilters(EloquentBuilder|QueryBuilder $query, FilterContext $context): EloquentBuilder|QueryBuilder
    {
        $customFilters = $context->getCustomFilters();

        foreach ($customFilters as $filterName => $value) {
            if (method_exists($this, $filterName)) {
                $this->$filterName($query, $value);
            }
        }

        return $query;
    }

    protected function applySorting(EloquentBuilder|QueryBuilder $query, array $sorts,$request): EloquentBuilder|QueryBuilder
    {
        if ($request->order) {
            $sorts = $this->getSortingFromRequest($request);
        }

        foreach ($sorts as $field => $direction) {
            if (in_array(strtolower($direction), ['asc', 'desc'])) {
                if (str_contains($field, '.')) {
                    // Handle relationship sorting
                    [$relation, $relationField] = explode('.', $field, 2);
                    // $query->with($relation)->orderBy(
                    //     $this->getRelationSubQuery($relation, $relationField),
                    //     $direction
                    // );
                    $query->whereHas($relation, function ($q) use ($relationField,$direction) {
                        $q->orderBy($relationField,$direction);
                    });
                } else {
                    $query->orderBy($field, $direction);
                }
            }
        }

        // dd($query->first()->user);
        return $query;
    }

    protected function formatResponse(
        EloquentBuilder|QueryBuilder $query,
        FilterContext $context,
        int $totalRecords,
        int $filteredRecords
    ) {
        $request = $context->getRequest();

        // DataTables format
        if ($request->has('draw')) {
            // return $query;
            return $this->formatDataTablesResponse(
                $query,
                $request,
                $totalRecords,
                $filteredRecords
            );
        }

        // API pagination format
        return $this->formatApiResponse(
            $query,
            $request,
            $totalRecords,
            $filteredRecords
        );
    }

    protected function formatDataTablesResponse(
        EloquentBuilder|QueryBuilder $query,
        Request $request,
        int $totalRecords,
        int $filteredRecords
    ): array {
        $length = (int) $request->input('length', 10);
        $start = (int) $request->input('start', 0);

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $data = $query->get();

        return [
            'status_code' => 1,
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ];
    }


    protected function formatApiResponse(
        EloquentBuilder|QueryBuilder $query,
        Request $request,
        int $totalRecords,
        int $filteredRecords
    ): array {
        $perPage = $request->input('per_page', 10);

        if ($request->boolean('all') || (int)$perPage === 0) {
            $data = $query->get();
            return [
                'status_code' => 1,
                'status' => 'success',
                'message' => 'All data retrieved successfully',
                'total' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $totalRecords,
                'data' => $data,
            ];
        }

        $paginated = $query->paginate($perPage);
        return [
            'status_code' => 1,
            'status' => 'success',
            'message' => 'Data retrieved successfully',
            'total' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $perPage,
            'data' => $paginated->items(),
        ];
    }

    // Helper methods
    protected function mapOperator(string $operator): string
    {
        return match ($operator) {
            'equal', 'eq' => '=',
            'not_equal', 'ne' => '!=',
            'greater', 'gt' => '>',
            'less', 'lt' => '<',
            'greater_or_equal', 'gte' => '>=',
            'less_or_equal', 'lte' => '<=',
            'like', 'contains' => 'like',
            'not_like', 'not_contains' => 'not like',
            'starts_with' => 'like',
            'ends_with' => 'like',
            'in' => 'in',
            'not_in' => 'not_in',
            'between' => 'between',
            'null', 'is_null' => 'null',
            'not_null', 'is_not_null' => 'not_null',
            default => '='
        };
    }

    protected function prepareValue($value, string $operator)
    {
        return match ($operator) {
            'like', 'not like' => "%{$value}%",
            'starts_with' => "{$value}%",
            'ends_with' => "%{$value}",
            default => $value
        };
    }

    protected function shouldSkipFilter(string $field, $value): bool
    {
        return in_array($field, ['search', 'columns', 'filter_group', 'draw', 'start', 'length', 'order', 'per_page', 'all', 'page', 'sort', 'all_parent', '_', 'action']) ||
            $value === null ||
            $value === '';
    }

    protected function isRangeFilter(string $field): bool
    {
        return str_ends_with($field, '_range') ||
            in_array($field, ['price', 'amount', 'total', 'quantity']);
    }

    protected function applySimpleFilter(EloquentBuilder|QueryBuilder $query, string $field, $value): void
    {
        $exactMatchFields = [
            'id',
            'user_id',
            'status',
            'type',
            'menu_type_id',
            'brand_id',
            'category_id',
        ];

        if (in_array($field, $exactMatchFields)) {
            $query->where($field, $value);
        } else {
            $query->where($field, 'like', "%{$value}%");
        }
    }

    protected function getSortingFromRequest(Request $request): array
    {
        $sorts = [];
        if ($request->has('order')) {
            foreach ($request->input('order') as $sort) {
                $columnIndex = $sort['column'];
                $direction = $sort['dir'];
                $columnName = $request->input("columns.{$columnIndex}.data");
                $sorts[$columnName] = $direction;
            }
        }
        return $sorts;
    }

    protected function getRelationSubQuery(string $relation, string $field)
    {
        // Implementation for relationship sorting subquery
        return $relation . '.' . $field;
    }

    // Custom filter methods (examples)
    protected function filterState(EloquentBuilder|QueryBuilder $query, $value): void
    {
        $query->whereHas('address', fn($q) => $q->where('state', $value));
    }

    protected function filterStatus(EloquentBuilder|QueryBuilder $query, $value): void
    {
        $query->where('status', $value);
    }

    protected function filterDateRange(EloquentBuilder|QueryBuilder $query, $value): void
    {
        if (is_string($value) && str_contains($value, ',')) {
            [$startDate, $endDate] = explode(',', $value);
            $query->whereBetween('created_at', [trim($startDate), trim($endDate)]);
        } elseif (is_array($value) && count($value) === 2) {
            $query->whereBetween('created_at', $value);
        }
    }

    protected function filterUserType(EloquentBuilder|QueryBuilder $query, $value): void
    {
        $query->whereHas('roles', fn($q) => $q->where('role_id', $value));
    }

    protected function filterPriceRange(EloquentBuilder|QueryBuilder $query, $value): void
    {
        if (is_array($value) && count($value) === 2) {
            $query->whereBetween('price', $value);
        }
    }

    protected function getModelFromQuery(EloquentBuilder|QueryBuilder $query): ?string
    {
        if ($query instanceof EloquentBuilder) {
            return get_class($query->getModel());
        }
        return null;
    }

    protected function validateColumnsExist(array $columns, Model|string $model): void
    {
        $table = is_string($model) ? (new $model())->getTable() : $model->getTable();

        $tableColumns = Schema::getColumnListing($table);

        foreach ($columns as $field => $column) {
            if (str_contains($column, '.') || $column === 'all' || $column === 'per_page' || $column === 'page' || $column === 'all_parent' || $column === '_' || $column === 'action') {
                continue;
            }

            if (!in_array($column, $tableColumns)) {
                abort(422, "Invalid filter: Column '{$column}' does not exist in the {$table} table.");
            }
        }
    }
}

// Supporting class for better organization
class FilterContext
{
    protected Request $request;
    protected array $options;

    public function __construct(Request $request, array $options = [])
    {
        $this->request = $request;
        $this->options = $options;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getSearchTerm(): ?string
    {
        $search = $this->request->input('search.value')
            ?? $this->request->input('search')
            ?? null;

        // If it's an array, try to extract the "value" key
        if (is_array($search)) {
            return $search['value'] ?? null;
        }

        return is_string($search) ? $search : null;
    }


    public function getSearchableColumns(): array
    {
        $columns = $this->request->input('columns', []);

        return array_values(array_filter(
            array_map(fn($col) => $col['data'] ?? null, $columns),
            fn($col) => $col
                && ($col !== 'action')   // skip "action" column
                && ($col !== '_')        // skip underscore if DataTables sends it
        ));
    }


    public function getFilterGroup(): ?array
    {
        return $this->request->input('filter_group');
    }

    public function getFieldFilters(): array
    {
        $excludeKeys = ['search', 'columns', 'filter_group', 'draw', 'start', 'length', 'order', 'sorts'];
        return $this->request->except($excludeKeys);
    }

    public function getRelationshipFilters(): array
    {
        return $this->options['relationship_filters'] ?? [];
    }

    public function getCustomFilters(): array
    {
        return $this->options['custom_filters'] ?? [];
    }

    public function getSorts(): array
    {
        return $this->request->input('sorts', []);
    }
}

// Helper function
if (!function_exists('camelCase')) {
    function camelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
    }
}
