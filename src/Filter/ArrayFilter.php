<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class ArrayFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     *
     * @return array<mixed> The value
     */
    public function __invoke($value)
    {
        return (array)$value;
    }
}
