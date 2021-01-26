<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class IntegerFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     *
     * @return int The result
     */
    public function __invoke($value)
    {
        return (int)$value;
    }
}
