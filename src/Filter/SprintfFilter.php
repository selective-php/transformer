<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class SprintfFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     * @param string $format The format
     *
     * @return string The value
     */
    public function __invoke($value, string $format)
    {
        return sprintf($format, $value);
    }
}
