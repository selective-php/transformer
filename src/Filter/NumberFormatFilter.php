<?php

namespace Selective\Transformer\Filter;

/**
 * Filter.
 */
final class NumberFormatFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     * @param int $decimals
     * @param string $decimalSeparator
     * @param string $thousandsSeparator
     *
     * @return mixed The value
     */
    public function __invoke(
        $value,
        int $decimals = 0,
        string $decimalSeparator = '.',
        string $thousandsSeparator = ','
    ) {
        return number_format($value, $decimals, $decimalSeparator, $thousandsSeparator);
    }
}
