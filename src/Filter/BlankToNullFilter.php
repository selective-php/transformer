<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class BlankToNullFilter
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
        return $value === '' ? null : (string)$value;
    }
}
