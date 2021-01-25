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
     * @param mixed $value
     *
     * @return mixed The result
     */
    public function __invoke($value)
    {
        return (int)$value;
    }
}
