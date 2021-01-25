<?php

namespace Selective\Transformer;

use Selective\Transformer\Exceptions\ArrayTransformerException;

final class ArrayValueConverter
{
    /**
     * @var callable[]
     */
    private $filters = [];

    /**
     * @param mixed $value
     * @param ArrayTransformerRule $rule
     *
     * @return mixed
     */
    public function convert($value, ArrayTransformerRule $rule)
    {
        if ($value === null) {
            return null;
        }
        foreach ($rule->getFilters() as $filter) {
            $name = $filter->getName();

            if (!isset($this->filters[$name])) {
                throw new ArrayTransformerException(sprintf('Filter not found: %s', $name));
            }

            $value = $this->invokeCallback($name, $value, $filter->getParams());
        }

        return $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param array<mixed> $parameters
     *
     * @return mixed
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
     * @param string $name
     * @param callable|string $callback
     */
    public function registerFilter(string $name, $callback): void
    {
        if (is_string($callback) && class_exists($callback)) {
            $callback = new $callback();
        }

        $this->filters[$name] = $callback;
    }
}
