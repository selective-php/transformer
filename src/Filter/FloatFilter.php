<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class FloatFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value
     *
     * @return mixed The value
     */
    public function __invoke($value)
    {
        return (float)$value;
    }
}
