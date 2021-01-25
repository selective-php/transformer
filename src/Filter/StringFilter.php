<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class StringFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     *
     * @return mixed The value
     */
    public function __invoke($value)
    {
        return (string)$value;
    }
}
