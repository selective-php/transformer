<?php

namespace Selective\Transformer;

use Selective\Transformer\Exceptions\ArrayTransformerException;

/**
 * Converter.
 */
final class ArrayValueConverter
{
    /**
     * @var callable[]
     */
    private $filters = [];

    /**
     * Convert the values by the given filter rules.
     *
     * @param mixed $value The source value
     * @param ArrayTransformerRule $rule The rule
     *
     * @return mixed The new value
     */
    public function convert($value, ArrayTransformerRule $rule)
    {
        if ($value === null) {
            return null;
        }

        foreach ($rule->getFiltersItems() as $filter) {
            $name = $filter->getName();

            if (!isset($this->filters[$name])) {
                throw new ArrayTransformerException(sprintf('Filter not found: %s', $name));
            }

            $value = $this->invokeCallback($name, $value, $filter->getArguments());
        }

        return $value;
    }

    /**
     * Invoke filter callback.
     *
     * @param string $name The filter name
     * @param mixed $value The value for the filter
     * @param array $parameters The filter arguments (optional)
     *
     * @return mixed The filter result
     */
    private function invokeCallback(string $name, $value, array $parameters = [])
    {
        $args = [$value];

        foreach ($parameters as $parameter) {
            $args[] = $parameter;
        }

        return call_user_func_array($this->filters[$name], $args);
    }

    /**
     * Register a filter callback.
     *
     * @param string $name The filter name
     * @param callable $callback The callback
     */
    public function registerFilter(string $name, callable $callback): void
    {
        $this->filters[$name] = $callback;
    }
}
