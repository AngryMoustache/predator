<?php

namespace AngryMoustache\Predator;

use AngryMoustache\Predator\Facades\Predator;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class PredatorFilter
{
    public array $item_type = [];
    public array $filters = [];
    public array $fields = [];
    public array $weights = [];
    public array $orderBy = [];

    /**
     * Start a new query
     * @param array|string $item_type Te item types to return
     * @return PredatorFilter
     */
    public static function query(...$item_type)
    {
        $filter = new static;
        $filter->item_type = $item_type;

        return $filter;
    }

    /**
     * Set the weights.
     * @param array $weights The weights for the filters
     * @return PredatorFilter
     */
    public function weights($weights)
    {
        $this->weights = Arr::wrap($weights);

        return $this;
    }

    /**
     * And an AND filter to the currently editing filter group.
     * @param mixed $key Filter key
     * @param mixed $operator Filter operator
     * @param mixed $value Filter value
     * @param bool $newGroup Create a new filter group
     * @return PredatorFilter
     */
    public function where($key, $operator = null, $value = null, $newGroup = true)
    {
        // Create a new group and set the filters inside
        if ($key instanceof Closure && is_null($operator)) {
            return $key($this);
        }

        // Get the correct operator/value if they are not set
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }

        // Normalize collections to arrays
        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        // Start a new filter group
        if ($newGroup) {
            $this->filters[] = [];
        }

        // Add the filter to the current group
        $this->filters[count($this->filters) - 1][] = [$key, $operator, json_encode($value)];

        return $this;
    }

    /**
     * Chain to the where filter without creating a new group.
     * @param mixed $key Filter key
     * @param mixed $operator Filter operator
     * @param mixed $value Filter value
     * @return PredatorFilter
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        return $this->where($key, $operator, $value, false);
    }

    /**
     * Order the results by the given key and direction.
     * @param string $key Key to sort on
     * @param string $direction Direction to sort on (asc or desc)
     * @return PredatorFilter
     */
    public function orderBy($key, $direction = 'asc')
    {
        $this->orderBy[$key] = $direction;

        return $this;
    }

    /**
     * Order the results by the given key in descending order.
     * @param string $key Key to sort on
     * @return PredatorFilter
     */
    public function orderByDesc($key)
    {
        return $this->orderBy($key, 'desc');
    }

    /**
     * Order the results by the given key and direction.
     * @param string $key Key to sort on
     * @param string $direction Direction to sort on (asc or desc)
     * @return PredatorFilter
     */
    public function fields(...$fields)
    {
        $this->fields = Arr::flatten($fields);

        return $this;
    }

    /**
     * Fetch the filter results with count
     * @return object
     */
    public function get()
    {
        return Predator::filter(
            $this->item_type,
            $this->filters,
            $this->weights,
            $this->orderBy,
            $this->fields
        );
    }

    /**
     * Fetch the filter results
     * @return \Illuminate\Support\Collection
     */
    public function results()
    {
        return collect($this->get()['results']);
    }

    /**
     * Dump and die
     * @return void
     */
    public function dd()
    {
        dd($this);
    }
}
