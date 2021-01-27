<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class StringWithBlankFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     *
     * @return string The value
     */
    public function __invoke($value)
    {
        return (string)$value;
    }
}
