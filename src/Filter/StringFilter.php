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
     * @return string|null The value
     */
    public function __invoke($value)
    {
        return $value === '' ? null : (string)$value;
    }
}
