<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class CallbackFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     * @param callable $callback The callback
     *
     * @return mixed The value
     */
    public function __invoke($value, callable $callback)
    {
        return call_user_func($callback, $value);
    }
}
