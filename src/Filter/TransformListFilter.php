<?php

namespace Selective\Transformer\Filter;

use Selective\Transformer\ArrayTransformer;

/**
 * Filter.
 */
final class TransformListFilter
{
    /**
     * Invoke.
     *
     * @param array $values The values
     * @param callable $callback The callback
     *
     * @return array|null The value
     */
    public function __invoke(array $values, callable $callback)
    {
        if (!$values) {
            // The item will be skipped if "required" is not set
            return null;
        }

        // Create transformer with callback
        $transformer = new ArrayTransformer();
        $callback($transformer);

        $target = [];

        // Apply rules to all array items
        foreach ($values as $item) {
            $target[] = $transformer->toArray($item);
        }

        return $target;
    }
}
