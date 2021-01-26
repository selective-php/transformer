<?php

namespace Selective\Transformer\Filter;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Selective\Transformer\Exceptions\ArrayTransformerException;

/**
 * Filter.
 */
final class DateTimeFilter
{
    /**
     * Invoke.
     *
     * @param mixed $value The value
     * @param string|null $format The date time format
     * @param DateTimeZone|null $timezone The time zone
     *
     * @throws ArrayTransformerException
     *
     * @return string The value
     */
    public function __invoke($value, string $format = null, DateTimeZone $timezone = null)
    {
        try {
            $format = $format ?? 'Y-m-d H:i:s';

            if ($value instanceof DateTimeImmutable) {
                return $this->formatDateTime($value, $format, $timezone);
            }

            return (string)(new DateTimeImmutable($value, $timezone))->format($format);
        } catch (Exception $exception) {
            throw new ArrayTransformerException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Format date time.
     *
     * @param DateTimeImmutable $value The date time
     * @param string $format The format
     * @param DateTimeZone|null $timezone The timezone
     *
     * @throws ArrayTransformerException
     *
     * @return string The result
     */
    private function formatDateTime(
        DateTimeImmutable $value,
        string $format,
        DateTimeZone $timezone = null
    ): string {
        if ($timezone) {
            // This would only with only work with UTC as default time zone.
            // https://3v4l.org/YlGWY
            throw new ArrayTransformerException(
                'Changing the DateTimeZone of an existing DateTimeImmutable object is not supported.'
            );
        }

        return (string)$value->format($format);
    }
}
