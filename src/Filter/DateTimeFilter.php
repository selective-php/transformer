<?php

namespace Selective\Transformer\Filter;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Filter.
 */
final class DateTimeFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value
     * @param string|null $format
     * @param DateTimeZone|null $timezone
     *
     * @return mixed The value
     */
    public function __invoke($value, string $format = null, DateTimeZone $timezone = null)
    {
        $format = $format ?? 'Y-m-d H:i:s';

        if (is_string($value)) {
            return (string)(new DateTimeImmutable($value, $timezone))->format($format);
        }

        if ($value instanceof DateTimeImmutable) {
            return (string)$value->format($format);
        }

        // Invalid value
        return null;
    }
}
