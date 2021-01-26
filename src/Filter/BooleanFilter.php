<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class BooleanFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     *
     * @return bool The value
     */
    public function __invoke($value)
    {
        return (bool)$value;
    }
}
